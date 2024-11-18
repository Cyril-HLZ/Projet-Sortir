<?php

namespace App\Controller;

use App\Entity\Etat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET'])]
    public function home(EntityManagerInterface $entityManager): Response
    {
        $etats = $entityManager->getRepository(Etat::class)->findAll();
        return $this->render('main/home.html.twig', [
            'etats' => $etats
        ]);
    }
}
