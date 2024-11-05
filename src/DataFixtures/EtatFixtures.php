<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Chargement table BDD "Etat"
        $etatsLibelles=['Créée', 'Ouverte', 'Clôturée', 'En cours', 'Passée', 'Annulée', 'Archivée'];

        foreach ($etatsLibelles as $index => $libelle){

            $etat = new Etat();
            $etat->setLibelle($libelle);
            $manager->persist($etat);

            $this->addReference('etat_'.$index, $etat);
        }

        $manager->flush();
    }
}
