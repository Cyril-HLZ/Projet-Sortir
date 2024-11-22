<?php

namespace App\Controller;


use App\Entity\Participant;
use App\Entity\Sortie;

use App\Form\SortieType;

use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    public function __construct(private readonly SortieRepository $sortieRepository)
    {
    }

    #[Route('/create', name: 'app_sortie_create', methods: ['GET', 'POST'])]
    public function create(Request        $request, EntityManagerInterface $entityManager, VilleRepository $villeRepository,
                           LieuRepository $lieuRepository, EtatRepository $etatRepository): Response
    {
        $participant = $this->getUser();

        $villes = $villeRepository->findAll();

        $sortie = new Sortie();
        $sortie->setOrganisateur($participant);
        $sortie->setCampus($participant->getCampus());
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->get('action') === 'publier') {
                $etatOuverte = $etatRepository->findOneBy(["libelle" => "Ouverte"]);
                $sortie->setEtat($etatOuverte);
            } else {
                $etatCreer = $etatRepository->findOneBy(["libelle" => "Creer"]);
                $sortie->setEtat($etatCreer);
            }

            $lieuId = $request->request->get('lieu');
            if ($lieuId) {
                $lieu = $lieuRepository->find($lieuId);
                if ($lieu) {
                    $sortie->setLieu($lieu);

                    $entityManager->persist($sortie);
                    $entityManager->flush();

                    $this->addFlash('success', "La sortie a été créée avec succès !");
                    return $this->redirectToRoute('main_home');
                }
            } else {
                $this->addFlash('error', "Veuillez sélectionner un lieu pour la sortie");
            }
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form->createView(),
            'villes' => $villes,
            'lieux' => [],
        ]);
    }

    #[Route('/api/lieux/{villeId}', name: 'api_lieux', methods: ['GET'])]
    public function getLieux(int $villeId, LieuRepository $lieuRepository): JsonResponse
    {
        $lieux = $lieuRepository->findBy(['ville' => $villeId]);

        $lieuxArray = [];
        foreach ($lieux as $lieu) {
            $lieuxArray[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
                'rue' => $lieu->getRue(),
                'code_postal' => $lieu->getVille()->getCodePostal(),
                'latitude' => $lieu->getLatitude(),
                'longitude' => $lieu->getLongitude(),
            ];
        }

        return new JsonResponse($lieuxArray);
    }

    #[Route('/{id}', name: 'app_sortie_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show($id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie inexistante');
        }

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request         $request, Sortie $sortie, EntityManagerInterface $entityManager,
                         VilleRepository $villeRepository,
                         LieuRepository  $lieuRepository): Response
    {
        $villes = $villeRepository->findAll();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid() && $this->getUser() === $sortie->getOrganisateur()) {
                $lieuId = $request->request->get('lieu');
                if ($lieuId) {
                    $lieu = $lieuRepository->find($lieuId);
                    $sortie->setLieu($lieu);
                }

                $entityManager->persist($sortie);
                $entityManager->flush();

                $this->addFlash('success', "La sortie a été mise à jour avec succès !");
            } else {
                $this->addFlash('danger', "Vous ne pouvez pas modifier la sortie !");
            }
            return $this->redirectToRoute('main_home');
        }
        return $this->render('sortie/edit.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
            'villes' => $villes,
        ]);
    }


    #[Route('/{id}', name: 'app_sortie_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, $id, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/organisateur/{id}', name: 'app_organisateur_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showOrganisateur(Participant $organisateur): Response
    {
        return $this->render('organisateur/show.html.twig', [
            'organisateur' => $organisateur,
        ]);
    }

    #[Route('/{id}/participant/{participantId}/remove', name: 'app_sortie_participant_remove', requirements: ['id' => '\d+', 'participantId' => '\d+'], methods: ['POST'])]
    public function removeParticipant(
        int                    $id,
        int                    $participantId,
        SortieRepository       $sortieRepository,
        ParticipantRepository  $participantRepository,
        EntityManagerInterface $entityManager,
        Request                $request
    ): Response
    {
        $sortie = $sortieRepository->find($id);
        $participant = $participantRepository->find($participantId);

        // Vérification que la sortie et le participant existent
        if (!$sortie || !$participant) {
            throw $this->createNotFoundException('Sortie ou participant inexistant');
        }

        // Vérification si le participant est inscrit à la sortie
        if (!$sortie->getParticipants()->contains($participant)) {
            $this->addFlash('warning', 'Le participant n\'est pas inscrit à cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $id]);
        }

        // Vérification CSRF
        if (!$this->isCsrfTokenValid('remove_participant' . $participantId, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Action non autorisée.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $id]);
        }

        // Retrait du participant
        $sortie->removeParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Participant retiré de la sortie avec succès.');
        return $this->redirectToRoute('app_sortie_show', ['id' => $id]);
    }
}