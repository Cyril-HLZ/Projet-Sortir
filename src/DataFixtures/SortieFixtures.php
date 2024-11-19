<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {   $faker = \Faker\Factory::create('fr_FR');

        for ($i = 1; $i <= 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence(3));

            // Campus
            $campusNames = ["SAINT HERBLAIN", "CHARTRES DE BRETAGNE", "LA ROCHE SUR YON"];
            $randomCampus = $campusNames[array_rand($campusNames)];
            $sortie->setCampus($this->getReference("campus".$randomCampus));

            $sortie->setOrganisateur($this->getReference("participant_organisateur"));

            $dateCreated = $faker->dateTimeBetween('-2 years', 'now');
            $sortie->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateCreated));
            $sortie->setDur�ee(210);


            $sortie->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateCreated->modify('+1 day')));
            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 20));
            $sortie->setInfosSortie($faker->text());

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
        ];
    }
}