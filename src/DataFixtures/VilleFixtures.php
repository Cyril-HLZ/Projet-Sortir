<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $villes = [
            ['Nantes', '44000'],
            ['Rennes', '35000'],
            ['La Roche sur Yon', '85000'],
            ['Saint-Herblain', '44800'],
            ['Chartres de Bretagne', '35131']
        ];

        foreach ($villes as $i => $villeData) {
            $ville = new Ville();
            $ville->setNom($villeData[0]);
            $ville->setCodePostal($villeData[1]);

            $manager->persist($ville);
            $this->addReference('ville'.$i, $ville);
        }

        $manager->flush();
    }
}