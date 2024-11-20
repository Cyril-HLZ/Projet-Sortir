<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\HomeFilterType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/home', name: 'main_home', methods: ['GET', 'POST'])]
    public function home( Request $request, SortieRepository $repository): Response
    {
        $filterForm = $this ->createForm(HomeFilterType::class);
        $filterForm->handleRequest($request);
        $sorties=[];

        dump($filterForm);
        dump($filterForm->createView());

        if($filterForm->isSubmitted() && $filterForm->isValid()){
            $criteria = $filterForm->getData();
            $user = $this ->getUser();
            $sorties = $repository->findByFilter($criteria, $user);
        }else{
            $sorties = $repository->findAll();
        }

        return $this->render('main/home.html.twig', [
            'filterForm' => $filterForm->createView(),
            'sorties' => $sorties,
        ]);
    }
}
