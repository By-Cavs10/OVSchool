<?php

namespace App\Manager;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SortieRepository;
use Psr\Log\LoggerInterface;

class SortieManager
{
    public function __construct(private EntityManagerInterface $entityManager, private SortieRepository $sortieRepository, private EtatRepository $etatRepository, private LoggerInterface $logger)
    {
    }

    public function openSorties(): int {
        $etatOuverte= $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);

        $sorties =$this->sortieRepository->findSortiesToPublish();

        self::updateEtatInSorties($sorties, $etatOuverte);

        return count($sorties);
    }

    public function clotureSorties(): int {
        $etatCloturee= $this->etatRepository->findOneBy(['libelle' => 'Clôturée']);

        $sorties =$this->sortieRepository->findSortiesToClose();

        self::updateEtatInSorties($sorties, $etatCloturee);

        return count($sorties);
    }

    public function beginSorties(): int {
        $etatEnCours= $this->etatRepository->findOneBy(['libelle' => 'En cours']);

        $sorties = $this->sortieRepository->findSortiesToBegin();

        self::updateEtatInSorties($sorties, $etatEnCours);

        return count($sorties);
    }

    public function finishSorties(): int {
        $etatTerminee= $this->etatRepository->findOneBy(['libelle' => 'Terminée']);

        $sorties = $this->sortieRepository->findSortiesToFinish();

        self::updateEtatInSorties($sorties, $etatTerminee);

        return count($sorties);
    }

    public function archiveSorties(): int {
        $etatArchivee = $this->etatRepository->findOneBy(['libelle' => 'Archivée']);

        $sorties =$this->sortieRepository->findSortiesToArchive();

        self::updateEtatInSorties($sorties, $etatArchivee);

        return count($sorties);
    }

    private function updateEtatInSorties(array $sorties, Etat $etat): void{
        foreach ($sorties as $sortie){
            if (!$sortie instanceof Sortie) {
                throw new \InvalidArgumentException('Tous les éléments doivent être des instances de Sortie.');
            }
            $sortie->setEtat($etat);
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();
    }

    public function updateAllSorties(): void {
        $int0 = $this->openSorties();
        $int1 = $this->clotureSorties();
        $int2 = $this->beginSorties();
        $int3 = $this->finishSorties();
        $int4 = $this->archiveSorties();
    }

}