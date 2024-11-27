<?php


namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    public function __construct(
        private readonly SortieService $sortieService
    )
    {
    }

    #[Route('/create', name: 'app_sortie_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Participant) {
            $this->addFlash('danger', 'Utilisateur non valide');
            return $this->redirectToRoute('main_home');
        }

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $this->sortieService->creer($sortie, $user, $request);
            if (empty($errors)) {
                $this->addFlash('success', "La sortie a été créée avec succès !");
                return $this->redirectToRoute('main_home');
            }
            foreach ($errors as $error) {
                $this->addFlash('danger', $error);
            }
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form->createView(),
            'villes' => $this->sortieService->getVilles(),
            'lieux' => [],
        ]);
    }

    #[Route('/api/lieux/{villeId}', name: 'api_lieux', methods: ['GET'])]
    public function getLieux(int $villeId): JsonResponse
    {
        return new JsonResponse($this->sortieService->getLieuxVille($villeId));
    }

    #[Route('/{id}', name: 'app_sortie_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/desistement', name: 'app_desistement', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function seDesister(Sortie $sortie): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Participant) {
            $this->addFlash('danger', 'Utilisateur non valide');
            return $this->redirectToRoute('main_home');
        }

        $errors = $this->sortieService->desister($sortie, $user);

        if (empty($errors)) {
            $this->addFlash('success', 'Vous vous êtes désisté de la sortie.');
        } else {
            foreach ($errors as $error) {
                $this->addFlash('danger', $error);
            }
        }

        return $this->redirectToRoute('main_home');
    }

    #[Route('/{id}/inscriptionSortie', name: 'app_inscription_sortie', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function inscriptionSortie(Sortie $sortie): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Participant) {
            $this->addFlash('danger', 'Utilisateur non valide');
            return $this->redirectToRoute('main_home');
        }

        $errors = $this->sortieService->inscrire($sortie, $user);

        if (empty($errors)) {
            $this->addFlash('success', 'Inscription réussie !');
        } else {
            foreach ($errors as $error) {
                $this->addFlash('danger', $error);
            }
        }

        return $this->redirectToRoute('main_home');
    }

    #[Route('/{id}/annulerSortie', name: 'app_annuler_sortie', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function annulerSortie(Sortie $sortie, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Participant) {
            $this->addFlash('danger', 'Utilisateur non valide');
            return $this->redirectToRoute('main_home');
        }

        if ($request->isMethod('POST')) {
            $motif = $request->request->get('motif');
            $errors = $this->sortieService->annuler($sortie, $user, $motif);

            if (empty($errors)) {
                $this->addFlash('success', 'La sortie a été annulée.');
                return $this->redirectToRoute('main_home');
            }

            foreach ($errors as $error) {
                $this->addFlash('danger', $error);
            }
        }

        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Participant) {
            $this->addFlash('danger', 'Utilisateur non valide');
            return $this->redirectToRoute('main_home');
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->get('action') === 'publier') {
                $errors = $this->sortieService->publier($sortie, $user, $request);
                if (empty($errors)) {
                    $this->addFlash('success', "La sortie a été publiée avec succès !");
                    return $this->redirectToRoute('main_home');
                }
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error);
                }
            } else {
                $errors = $this->sortieService->modifier($sortie, $user, $request);
                if (empty($errors)) {
                    $this->addFlash('success', "La sortie a été mise à jour avec succès !");
                    return $this->redirectToRoute('main_home');
                }
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error);
                }
            }
        }

        return $this->render('sortie/edit.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
            'villes' => $this->sortieService->getVilles(),
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Participant) {
            $this->addFlash('danger', 'Utilisateur non valide');
            return $this->redirectToRoute('main_home');
        }

        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->request->get('_token'))) {
            $errors = $this->sortieService->supprimer($sortie, $user);

            if (empty($errors)) {
                $this->addFlash('success', 'La sortie a été supprimée.');
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error);
                }
            }
        }

        return $this->redirectToRoute('main_home');
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