<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantFilterType;
use App\Form\ParticipantFormType;
use App\Form\UploadExcelType;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListeController extends AbstractController
{
    #[Route('/liste', name: 'app_participants_list', methods: ['GET', 'POST'])]
    public function listAndImport(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        // --- 1. Gestion des filtres ---
        $filterForm = $this->createForm(ParticipantFilterType::class);
        $filterForm->handleRequest($request);

        $queryBuilder = $entityManager->getRepository(Participant::class)->createQueryBuilder('p');

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();

            if (!empty($data['nom'])) {
                $queryBuilder->andWhere('p.nom LIKE :nom')
                    ->setParameter('nom', '%' . $data['nom'] . '%');
            }

            if (!empty($data['prenom'])) {
                $queryBuilder->andWhere('p.prenom LIKE :prenom')
                    ->setParameter('prenom', '%' . $data['prenom'] . '%');
            }

            if (!empty($data['dateMin'])) {
                $queryBuilder->andWhere('p.createdAt >= :dateMin')
                    ->setParameter('dateMin', $data['dateMin']);
            }

            if (!empty($data['dateMax'])) {
                $queryBuilder->andWhere('p.createdAt <= :dateMax')
                    ->setParameter('dateMax', $data['dateMax']);
            }
        }

        $participants = $queryBuilder->getQuery()->getResult();

        // --- 2. Gestion de l'importation ---
        $importForm = $this->createForm(UploadExcelType::class);
        $importForm->handleRequest($request);

        if ($importForm->isSubmitted() && $importForm->isValid()) {
            $file = $importForm->get('file')->getData();

            if ($file) {
                try {
                    $spreadsheet = IOFactory::load($file->getPathname());
                    $sheet = $spreadsheet->getActiveSheet();
                    $data = $sheet->toArray();

                    foreach ($data as $index => $row) {
                        if ($index === 0) continue; // Ignorer la ligne d'entête
                        $participant = new Participant();
                        $participant->setNom($row[0]);
                        $participant->setPrenom($row[1]);
                        $participant->setMail($row[2]);
                        $participant->setAdministrateur((bool)$row[3]);
                        $participant->setActif((bool)$row[4]);

                        $entityManager->persist($participant);
                    }

                    $entityManager->flush();
                    $this->addFlash('success', 'Participants importés avec succès !');
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'importation : ' . $e->getMessage());
                }

                return $this->redirectToRoute('app_participants_list');
            }
        }

        // --- 3. Gestion des désactivations ---
        if ($request->isMethod('POST') && $request->request->has('selectedParticipant')) {
            $selectedIds = $request->request->all('selectedParticipant', []);
            if (!empty($selectedIds)) {
                $participantsToDeactivate = $entityManager->getRepository(Participant::class)->findBy(['id' => $selectedIds]);

                foreach ($participantsToDeactivate as $participant) {
                    $participant->setActif(false);
                    $entityManager->persist($participant);
                }

                $entityManager->flush();
                $this->addFlash('success', 'Les participants sélectionnés ont été désactivés.');
            } else {
                $this->addFlash('danger', 'Aucun participant sélectionné pour désactivation.');
            }
        }

        // --- 4. Gestion des suppressions ---
        if ($request->request->has('deleteParticipantId')) {
            $participantId = $request->request->get('deleteParticipantId');
            $participant = $entityManager->getRepository(Participant::class)->find($participantId);

            if ($participant) {
                $entityManager->remove($participant);
                $entityManager->flush();
                $this->addFlash('success', 'Le participant a été supprimé avec succès.');
            } else {
                $this->addFlash('danger', 'Le participant à supprimer n\'existe pas.');
            }
        }

        return $this->render('liste/liste.html.twig', [
            'filterForm' => $filterForm->createView(),
            'importForm' => $importForm->createView(),
            'participants' => $participants,
        ]);
    }

    #[Route('/participant/edit/{id}', name: 'app_participants_edit', methods: ['GET','POST'])]
    public function editParticipant(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = $entityManager->getRepository(Participant::class)->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé');
        }

        $form = $this->createForm(ParticipantFormType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if ($file) {
                $newFileName = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move($this->getParameter('upload_directory'), $newFileName);
                    $participant->setPhoto($newFileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Les informations du participant ont été mises à jour.');

            return $this->redirectToRoute('app_participants_list');
        }

        return $this->render('participant/edit.html.twig', [
            'form' => $form->createView(),
            'participant' => $participant,
        ]);
    }
}


