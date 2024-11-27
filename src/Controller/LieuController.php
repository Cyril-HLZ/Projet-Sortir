<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuxFormType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

    #[Route('/lieu')]
class LieuController extends AbstractController
{
    #[Route('/show', name: 'app_lieu_show', methods: ['GET', 'POST'])]
    public function show(LieuRepository $lieuRepository, Request $request,EntityManagerInterface $entityManager): Response
    {
        $lieux = $lieuRepository->findAll();
        $lieu = new Lieu();
        $formLieu = $this->createForm(LieuxFormType::class);
        $formLieu->handleRequest($request);
        if ($formLieu->isSubmitted() && $formLieu->isValid()) {
            $lieu = $formLieu->getData();
            $lieu->setNom($lieu->getNom());
            $lieu->setRue($lieu->getRue());
            $lieu->setVille($lieu->getVille());
            $lieu->setLongitude($lieu->getLongitude());
            $lieu->setLatitude($lieu->getLatitude());
            $entityManager->persist($lieu);
            $entityManager->flush();
            return $this->redirectToRoute('app_lieu_show');
        }
        return $this->render('lieu/show.html.twig', [
            'lieux' => $lieux,
            'formLieu' => $formLieu->createView(),
        ]);
    }
        #[Route('/{id}/delete', name: 'app_lieu_delete', methods: ['POST'])]
        public function delete(Request $request, Lieu $lieu, EntityManagerInterface $entityManager): Response
        {
            if ($this->isCsrfTokenValid('delete'.$lieu->getId(), $request->getPayload()->getString('_token'))) {
                if (!$lieu->getSorties()->isEmpty()) {
                    $this->addFlash('danger','Une sortie est programmé pour ce lieu, supprimer la sortie ou attendez qu\'elle soit passée');
                    return $this->redirectToRoute('main_home');
                }
                try {
                    $entityManager->remove($lieu);
                    $entityManager->flush();
                    $this->addFlash('success','Le lieu à été supprimer');

                }catch (\Exception $e){
                    $this->addFlash('danger','Une erreur est survenue lors de la suppression => '.$e->getMessage());
                }
            }

            return $this->redirectToRoute('ville_list', [], Response::HTTP_SEE_OTHER);
        }
}
