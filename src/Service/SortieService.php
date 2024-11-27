<?php


namespace App\Service;

use App\Entity\Sortie;
use App\Entity\Participant;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SortieService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EtatRepository         $etatRepository,
        private readonly SortieRepository       $sortieRepository,
        private readonly ParticipantRepository  $participantRepository,
        private readonly LieuRepository         $lieuRepository,
        private readonly VilleRepository        $villeRepository,
    )
    {
    }

    public function verifierDates(Sortie $sortie): array
    {
        $errors = [];
        $now = new \DateTime();

        if ($sortie->getDateHeureDebut() < $now) {
            $errors[] = "La date de début ne peut pas être dans le passé";
        }

        if ($sortie->getDateLimiteInscription() > $sortie->getDateHeureDebut()) {
            $errors[] = "La date limite d'inscription doit être avant la date de début";
        }

        if ($sortie->getDateLimiteInscription() < $now) {
            $errors[] = "La date limite d'inscription ne peut pas être dans le passé";
        }

        $dateFin = clone $sortie->getDateHeureDebut();
        $dateFin->modify('+' . $sortie->getDuree() . ' minutes');

        if ($dateFin < $sortie->getDateHeureDebut()) {
            $errors[] = "La durée doit être positive";
        }

        return $errors;
    }

    public function inscrire(Sortie $sortie, Participant $participant): array
    {
        $errors = [];
        $now = new \DateTime();

        if (!$this->peutSInscrire($sortie, $participant, $errors)) {
            return $errors;
        }

        $sortie->getParticipants()->add($participant);
        $participant->getSorties()->add($sortie);

        $this->entityManager->persist($sortie);
        $this->entityManager->persist($participant);
        $this->entityManager->flush();
        return [];
    }

    public function desister(Sortie $sortie, Participant $participant): array
    {
        $errors = [];
        if (!$this->peutSeDesister($sortie, $participant, $errors)) {
            return $errors;
        }

        $sortie->getParticipants()->removeElement($participant);
        $participant->getSorties()->removeElement($sortie);

        $this->entityManager->flush();
        return [];
    }

    public function annuler(Sortie $sortie, Participant $organisateur, string $motif): array
    {
        $errors = [];
        if (!$this->peutAnnuler($sortie, $organisateur, $errors)) {
            return $errors;
        }

        $etatAnnule = $this->etatRepository->findOneBy(['libelle' => 'Annulée']);
        $sortie->setEtat($etatAnnule);
        $sortie->setInfosSortie($motif);

        $this->entityManager->flush();
        return [];
    }

    private function peutSInscrire(Sortie $sortie, Participant $participant, array &$errors): bool
    {
        $now = new \DateTime();

        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $errors[] = "Les inscriptions sont fermées";
            return false;
        }

        if ($sortie->getParticipants()->contains($participant)) {
            $errors[] = "Vous êtes déjà inscrit";
            return false;
        }

        if (count($sortie->getParticipants()) >= $sortie->getNbInscriptionsMax()) {
            $errors[] = "La sortie est complète";
            return false;
        }

        if ($sortie->getDateLimiteInscription() < $now) {
            $errors[] = "La date limite d'inscription est dépassée";
            return false;
        }

        return true;
    }

    private function peutSeDesister(Sortie $sortie, Participant $participant, array &$errors): bool
    {
        $now = new \DateTime();

        if ($sortie->getEtat()->getLibelle() !== 'Ouverte' && $sortie->getEtat()->getLibelle() !== 'Créer') {
            $errors[] = "Impossible de se désister, la sortie n'est pas ouverte";
            return false;
        }

        if ($sortie->getOrganisateur() === $participant) {
            $errors[] = "Impossible de se désister, Vous êtes l'organisateur de l'evenement!";
            return false;
        }

        if (!$sortie->getParticipants()->contains($participant)) {
            $errors[] = "Vous n'êtes pas inscrit à cette sortie";
            return false;
        }

        if ($sortie->getDateHeureDebut() <= $now) {
            $errors[] = "La sortie a déjà commencé";
            return false;
        }

        return true;
    }

    private function peutAnnuler(Sortie $sortie, Participant $participant, array &$errors): bool
    {
        $now = new \DateTime();

        if (in_array('ROLE_ADMIN', $participant->getRoles())) {
            if ($sortie->getDateHeureDebut() <= $now) {
                $errors[] = "La sortie a déjà commencé";
                return false;
            }
            return true;
        }


        if ($sortie->getOrganisateur() !== $participant) {
            $errors[] = "Vous n'êtes pas l'organisateur de cette sortie";
            return false;
        }

        if ($sortie->getEtat()->getLibelle() === 'Annulée') {
            $errors[] = "La sortie est déjà annulée";
            return false;
        }

        if ($sortie->getDateHeureDebut() <= $now) {
            $errors[] = "La sortie a déjà commencé";
            return false;
        }

        return true;
    }


    public function creer(Sortie $sortie, Participant $participant, Request $request): array
    {
        $errors = $this->verifierDates($sortie);
        if (!empty($errors)) {
            return $errors;
        }


        $sortie->setOrganisateur($participant);
        $sortie->setCampus($participant->getCampus());
        $sortie->getParticipants()->add($participant);
        $participant->getSorties()->add($sortie);

        $lieuId = $request->request->get('lieu');
        if (!$lieuId) {
            return ['Veuillez sélectionner un lieu'];
        }

        $lieu = $this->lieuRepository->find($lieuId);
        if (!$lieu) {
            return ['Lieu non trouvé'];
        }

        $sortie->setLieu($lieu);

        if ($request->request->get('action') === 'publier') {
            $etatOuvert = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);
            $sortie->setEtat($etatOuvert);
        } else {
            $etatCree = $this->etatRepository->findOneBy(['libelle' => 'Creer']);
            $sortie->setEtat($etatCree);
        }

        $this->entityManager->persist($sortie);
        $this->entityManager->flush();

        return [];
    }

    public function getVilles(): array
    {
        return $this->villeRepository->findAll();
    }

    public function modifier(Sortie $sortie, Participant $participant, Request $request): array
    {
        if ($sortie->getOrganisateur() !== $participant && !in_array('ROLE_ADMIN', $participant->getRoles())) {
            return ['Vous ne pouvez pas modifier cette sortie'];
        }
        if ($sortie->getEtat()->getLibelle() === 'Annulée') {
            return ['Impossible de modifier une sortie annulée'];
        }

        $errors = $this->verifierDates($sortie);
        if (!empty($errors)) {
            return $errors;
        }

        $lieuId = $request->request->get('lieu');
        if ($lieuId) {
            $lieu = $this->lieuRepository->find($lieuId);
            if (!$lieu) {
                return ['Lieu non trouvé'];
            }
            $sortie->setLieu($lieu);
        }

        $this->entityManager->flush();
        return [];
    }

    public function publier(Sortie $sortie, Participant $participant, Request $request): array
    {
        $errors = $this->modifier($sortie, $participant, $request);
        if (!empty($errors)) {
            return $errors;
        }

        if ($sortie->getEtat()->getLibelle() !== 'Créer') {
            return ['La sortie doit être en état "Creer" pour être publiée'];
        }

        $etatOuvert = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);
        if (!$etatOuvert) {
            return ['État Ouverte non trouvé'];
        }

        $sortie->setEtat($etatOuvert);
        $this->entityManager->flush();

        return [];
    }


    public function getLieuxVille(int $villeId): array
    {
        $lieux = $this->lieuRepository->findBy(['ville' => $villeId]);
        $lieuxArray = [];
        foreach ($lieux as $lieu) {
            $lieuxArray[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
                'rue' => $lieu->getRue(),
                'code_postal' => $lieu->getVille()->getCodePostal(),
                'latitude' => $lieu->getLatitude(),
                'longitude' => $lieu->getLongitude(),
            ];
        }
        return $lieuxArray;
    }

    public function supprimer(Sortie $sortie, Participant $participant): array
    {
        if ($sortie->getOrganisateur() !== $participant && !in_array('ROLE_ADMIN', $participant->getRoles())) {
            return ['Vous n\'êtes pas l\'organisateur de cette sortie'];
        }

        if ($sortie->getEtat()->getLibelle() === 'Annulée') {
            return ['Impossible de supprimer une sortie annulée'];
        }

        if ($sortie->getEtat()->getLibelle() === 'Activité en cours' ||
            $sortie->getEtat()->getLibelle() === 'Passée') {
            return ['Impossible de supprimer une sortie en cours ou passée'];
        }

        try {
            $this->entityManager->remove($sortie);
            $this->entityManager->flush();
            return [];
        } catch (\Exception $e) {
            return ['Erreur lors de la suppression de la sortie'];
        }
    }

}