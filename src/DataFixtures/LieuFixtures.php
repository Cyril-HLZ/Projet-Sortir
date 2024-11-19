<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $lieux = [
            ["Salle de sport", "Rue du Sport"],
            ["Cinéma", "Avenue du Film"],
            ["Parc", "Allée des Jardins"],
            ["Restaurant", "Rue de la Gastronomie"],
            ["Bowling", "Boulevard des Loisirs"]
        ];

        foreach ($lieux as $i => $lieuData) {
            $lieu = new Lieu();
            $lieu->setNom($lieuData[0]);
            $lieu->setRue($lieuData[1]);
            $lieu->setLatitude($faker->latitude);
            $lieu->setLongitude($faker->longitude);
            $lieu->setVille($this->getReference('ville'.rand(0, 4)));

            $manager->persist($lieu);
            $this->addReference('lieu'.$i, $lieu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }
}