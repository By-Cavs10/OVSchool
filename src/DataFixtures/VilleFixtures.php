<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Chargement table BDD "Ville"
        $villeNoms=['Nantes', 'Rennes', 'Quimper', 'Niort', 'La Roche-Sur-Yon'];
        $villeCodesPostaux=['44000', '35000', '29000', '79000', '85000'];

        for ($i = 0 ; $i < count($villeNoms); ++$i){
            $ville = new Ville();
            $ville->setNom($villeNoms[$i]);
            $ville->setCodePostal(($villeCodesPostaux[$i]));
            $manager->persist($ville);

            $this->addReference('ville_'.$i, $ville);
        }

        $manager->flush();
    }
}
