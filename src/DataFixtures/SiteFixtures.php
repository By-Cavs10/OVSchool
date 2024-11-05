<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Chargement de la table "Site" dans la BDD
        $siteNoms = ['Nantes', 'Rennes', 'Quimper', 'Niort', 'La Roche-Sur-Yon', 'En ligne'];

        foreach ($siteNoms as $index => $nom) {
            $site = new Site();
            $site->setNom($nom);
            $manager->persist($site);

            $this->addReference('site_'.$index, $site);
        }

        $manager->flush();
    }
}
