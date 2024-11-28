<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusFormType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/campus')]
final class CampusController extends AbstractController
{
    #[Route('/', name: 'campus_list', methods: ['GET'])]
    public function list(CampusRepository $campusRepository): Response
    {
        $campuses = $campusRepository->findAll();

        return $this->render('campus/index.html.twig', [
            'campuses' => $campuses,
        ]);
    }

    #[Route('/create', name: 'campus_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, CampusRepository $campusRepository): Response
    {
        $campuses = $campusRepository->findAll();
        $campus = new Campus();
        $campusForm = $this->createForm(CampusFormType::class, $campus);
        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();

            $this->addFlash("success", "Le campus a bien été enregistré.");

            return $this->redirectToRoute('campus_list');
        }

        return $this->render('campus/create.html.twig', [
            "campusForm" => $campusForm,
            'campus' => $campus,
            'campuses' => $campuses,
        ]);
    }

    #[Route('/{id}/show', name: 'campus_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Campus $campus): Response
    {
        $participants = $campus->getParticipants();
        $sorties = $campus->getSorties();

        return $this->render('campus/show.html.twig', [
            'campus' => $campus,
            'participants' => $participants,
            'sorties' => $sorties,
        ]);
    }

    #[Route('/{id}/edit', name: 'campus_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Campus $campus, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CampusFormType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le campus a été mis à jour.');
            return $this->redirectToRoute('campus_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('campus/edit.html.twig', [
            'campus' => $campus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'campus_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Campus $campus, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $campus->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($campus);
                $entityManager->flush();

                $this->addFlash('success', 'Le campus a été supprimé.');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Une erreur est survenue lors de la suppression.');
            }
        }

        return $this->redirectToRoute('campus_list', [], Response::HTTP_SEE_OTHER);
    }
}
