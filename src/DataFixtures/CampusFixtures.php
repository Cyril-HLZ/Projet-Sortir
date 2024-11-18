<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $CampusNames = [
            "SAINT HERBLAIN",
            "CHARTRES DE BRETAGNE",
            "LA ROCHE SUR YON",
        ];
        foreach ($CampusNames as $campusName) {
            $campus = new Campus();
            $campus->setNom($campusName);
            $this->addReference("campus".$campusName, $campus);
            $manager->persist($campus);
        }
        $manager->flush();
    }
}
