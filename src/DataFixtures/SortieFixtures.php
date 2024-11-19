<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 1; $i <= 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence(3));

            // Liaison avec un campus existant
            $campusReference = "campus" . ["SAINT HERBLAIN", "CHARTRES DE BRETAGNE", "LA ROCHE SUR YON"][rand(0, 2)];
            $sortie->setCampus($this->getReference($campusReference));

            // Liaison avec un participant comme organisateur
            $sortie->setOrganisateur($this->getReference("participant" . rand(1, 10)));

            // Liaison avec un état
            $sortie->setEtat($this->getReference("etat" . rand(0, 5)));

            // Liaison avec un lieu
            $sortie->setLieu($this->getReference("lieu" . rand(0, 4)));

            $dateCreated = $faker->dateTimeBetween('-2 years', 'now');
            $sortie->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateCreated));
            $sortie->setDuree(210);
            $sortie->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateCreated->modify('+1 day')));
            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 20));
            $sortie->setInfosSortie($faker->text());

            // Ajout de participants aléatoires
            $nbParticipants = rand(1, 5);
            for ($j = 1; $j <= $nbParticipants; $j++) {
                $participant = $this->getReference("participant" . rand(1, 10));
                $sortie->addParticipant($participant);
            }

            $manager->persist($sortie);
            $this->addReference('sortie'.$i, $sortie);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
            ParticipantFixtures::class,
            EtatFixtures::class,
            LieuFixtures::class,
        ];
    }
}