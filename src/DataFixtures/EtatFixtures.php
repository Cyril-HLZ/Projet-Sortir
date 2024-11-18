<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $libelleNames = [
            "Créer",
            "Ouverte",
            "Clotûrée",
            "Activité en cours",
            "Passée",
            "Annulée"
        ];
        foreach ($libelleNames as $libelleName) {
            $etat = new Etat();
            $etat->setLibelle($libelleName);
            $manager->persist($etat);
        }

        $manager->flush();
    }
}
