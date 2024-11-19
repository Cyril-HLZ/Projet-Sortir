<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readOnly UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 1; $i <= 10; $i++) {
            $participant = new Participant();
            $participant->setNom($faker->lastName);
            $participant->setPrenom($faker->firstName);
            $participant->setTelephone($faker->phoneNumber);
            $participant->setMail($faker->email);
            $participant->setPassword($this->passwordHasher->hashPassword($participant, "PLANNER"));
            $participant->setAdministrateur(false);
            $participant->setActif(true);

            // Attribution aléatoire d'un campus
            $campusReference = "campus" . ["SAINT HERBLAIN", "CHARTRES DE BRETAGNE", "LA ROCHE SUR YON"][rand(0, 2)];
            $participant->setCampus($this->getReference($campusReference));

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