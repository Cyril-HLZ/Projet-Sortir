<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleFormType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ville')]

final class VilleController extends AbstractController
{
    #[Route('/', name: 'ville_list', methods: ['GET'])]
    public function list(VilleRepository $villeRepository): Response
    {
        $villes = $villeRepository->findAll();

        return $this->render('ville/index.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route('/create', name: 'ville_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, VilleRepository $villeRepository): Response
    {
        $villes = $villeRepository->findAll();
        $ville = new Ville();
        $villeform = $this->createForm(VilleFormType::class, $ville);
        $villeform->handleRequest($request);

        if ($villeform->isSubmitted() && $villeform->isValid()) {
            // On sauvegarde dans la BDD
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash("success", "La ville à bien été enregistré");

            return $this->redirectToRoute('main_home');
        }

        return $this->render('ville/create.html.twig', [
            "villeForm" => $villeform,
            'ville' => $ville,
            'villes' => $villes,
        ]);
    }

    #[Route('/{id}/show', name: 'app_ville_show', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function show(Ville $ville): Response
    {
        $lieux = $ville->getLieux();
        return $this->render('ville/show.html.twig', [
            'ville' => $ville,
            'lieux' => $lieux,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ville_edit', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VilleFormType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ville/edit.html.twig', [
            'ville' => $ville,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_ville_delete', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function delete(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ville->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($ville);
                $entityManager->flush();
                $this->addFlash('success', 'La ville à été supprimer');

            } catch (\Exception $e) {
                $this->addFlash('danger', 'Une erreur est survenue lors de la suppression. Veuillez supprimer le(s) lieu(x) associé(s)');
            }
        }

        return $this->redirectToRoute('ville_list', [], Response::HTTP_SEE_OTHER);
    }
}
