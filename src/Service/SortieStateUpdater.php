<?php
// src/Service/SortieStateUpdater.php
namespace App\Service;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class SortieStateUpdater
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EtatRepository $etatRepository,
        private SortieRepository $sortieRepository
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function updateEtats(): void
    {
        $now = new \DateTime();
        $oneMonthAgo = ($now->modify('-1 month'));
        $this->sortieRepository->deleteOldSorties($oneMonthAgo);

        $etatEnCours = $this->etatRepository->findOneBy(['libelle' => 'Activité en cours']);
        $etatPassee = $this->etatRepository->findOneBy(['libelle' => 'Passée']);
        $etatCloturee = $this->etatRepository->findOneBy(['libelle' => 'Cloturée']);

        $sorties = $this->sortieRepository->findAll();

        foreach ($sorties as $sortie) {
            if ($sortie->getEtat()->getLibelle() === 'Annulée') {
                continue;
            }

            $dateHeureDebut = $sortie->getDateHeureDebut();
            $dateLimiteInscription = $sortie->getDateLimiteInscription();
            $dateFin = clone $dateHeureDebut;
            $dateFin->modify('+' . $sortie->getDuree() . ' minutes');

            if ($dateFin < $now) {
                $sortie->setEtat($etatPassee);
            }
            elseif ($dateHeureDebut <= $now && $dateFin > $now) {
                $sortie->setEtat($etatEnCours);
            }
            elseif ($dateLimiteInscription < $now && $dateHeureDebut > $now) {
                $sortie->setEtat($etatCloturee);
            }
        }

        $this->entityManager->flush();
    }
}