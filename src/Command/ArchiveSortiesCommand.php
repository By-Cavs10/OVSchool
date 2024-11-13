<?php

namespace App\Command;

use App\Manager\SortieManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
#[AsCommand(
    name: 'sortie:archivee',
    description: 'Passe les sorties de l\'état "Terminée"/"Annulée" à l\'état "Archivée" quand la "dateHeureDebut" est passée de au moins un mois.',
)]
class ArchiveSortiesCommand extends Command
{
    public function __construct(private SortieManager $sortieManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nbSortiesArchivees = $this->sortieManager->archiveSorties();

        $output->writeln($nbSortiesArchivees.' sorties ont été archivées.');
        return Command::SUCCESS;
    }
}
