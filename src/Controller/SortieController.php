<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieFilterType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    public function __construct(private readonly SortieRepository $sortieRepository) {}

    /*
    #[Route('/', name: 'app_sortie_home', methods: ['GET'])]
    public function home(Request $request, SortieRepository $sortieRepository): Response
    {
        // Créez le formulaire de filtrage
        $filterForm = $this->createForm(SortieFilterType::class);
        $filterForm->handleRequest($request);

        // Récupérez les sorties
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $criteria = $filterForm->getData();
            $sorties = $sortieRepository->findByFilter($criteria, $this->getUser());
        } else {
            $sorties = $sortieRepository->findAll();
        }

        // Passez à la vue
        return $this->render('main/home.html.twig', [
            'form' => $filterForm->createView(),
            'sorties' => $sorties
        ]);
    }*/

    #[Route('/create', name: 'app_sortie_create', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde en base de données
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', "La sortie a été créée avec succès !");
            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', "La sortie a été mise à jour avec succès !");
            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('sortie/edit.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
        ]);
    }


    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }
}
