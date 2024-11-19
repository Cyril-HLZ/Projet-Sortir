<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {   $faker = \Faker\Factory::create('fr_FR');
        $campusNames = ["SAINT HERBLAIN", "CHARTRES DE BRETAGNE", "LA ROCHE SUR YON"];


        $organisateur = new Participant();
        $organisateur->setNom('Organisateur');
        $organisateur->setPrenom($faker->firstName);
        $organisateur->setTelephone($faker->phoneNumber);
        $organisateur->setMail('organisateur@test.com');
        $organisateur->setMotPasse('password');
        $organisateur->setAdministrateur(true);
        $organisateur->setActif(true);
        $organisateur->setCampus($this->getReference("campusSAINT HERBLAIN"));

        $manager->persist($organisateur);

        $this->addReference('participant_organisateur', $organisateur);



        for ($i = 1; $i <= 10; $i++) {
            $participant = new Participant();
            $participant->setNom($faker->lastName);
            $participant->setPrenom($faker->firstName);
            $participant->setTelephone($faker->phoneNumber);
            $participant->setMail($faker->email);
            $participant->setMotPasse('password');
            $participant->setAdministrateur(false);
            $participant->setActif(true);
            $participant->setCampus($this->getReference("campus".$campusNames[0]));

            $manager->persist($participant);
            $this->addReference('participant'.$i, $participant);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }
}