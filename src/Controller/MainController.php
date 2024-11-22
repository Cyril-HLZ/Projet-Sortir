<?php

namespace App\Controller;


use App\Form\HomeFilterType;
use App\Repository\SortieRepository;
use App\Service\SortieStateUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET', 'POST'])]
    public function home(Request            $request,
                         SortieRepository   $sortieRepository,
                         SortieStateUpdater $stateUpdater
    ): Response
    {
        $stateUpdater->updateEtats();
        $filterForm = $this->createForm(HomeFilterType::class);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $criteria = $filterForm->getData();
            $user = $this->getUser();
            $sorties = $sortieRepository->findByFilter($criteria, $user);
        } else {
            $sorties = $sortieRepository->findAll();
        }

        return $this->render('main/home.html.twig', [
            'filterForm' => $filterForm->createView(),
            'sorties' => $sorties,
        ]);
    }


}
