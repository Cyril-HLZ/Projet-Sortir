<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\UploadExcelType;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

class ListeController extends AbstractController
{
    #[Route('/liste', name: 'app_participants_list', methods: ['GET', 'POST'])]
    public function listAndImport(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Création du formulaire d'importation
        $form = $this->createForm(UploadExcelType::class);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, traiter le fichier
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            if ($file) {
                try {
                    // Charger le fichier Excel
                    $spreadsheet = IOFactory::load($file->getPathname());
                    $sheet = $spreadsheet->getActiveSheet();
                    $data = $sheet->toArray();

                    // Parcours des données du fichier
                    foreach ($data as $index => $row) {
                        // Ignorer la ligne d'entête (si présente)
                        if ($index === 0) {
                            continue;
                        }

                        $participant = new Participant();
                        $participant->setNom($row[0]);
                        $participant->setPrenom($row[1]);
                        $participant->setMail($row[2]);
                        $participant->setAdministrateur((bool) $row[3]);
                        $participant->setActif((bool) $row[4]);

                        $entityManager->persist($participant);
                    }

                    // Sauvegarde des données dans la base
                    $entityManager->flush();

                    $this->addFlash('success', 'Participants importés avec succès !');
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'importation : ' . $e->getMessage());
                }

                // Rediriger pour éviter une double soumission du formulaire
                return $this->redirectToRoute('app_participants');
            }
        }

        // Récupération de tous les participants
        $participants = $entityManager->getRepository(Participant::class)->findAll();

        // Rendre le formulaire et la liste des participants
        return $this->render('liste/liste.html.twig', [
            'form' => $form->createView(),
            'participants' => $participants,
        ]);
    }
}
