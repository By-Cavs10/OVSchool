<?php

namespace App\Command;

use App\Manager\SortieManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
#[AsCommand(
    name: 'sortie:terminee',
    description: 'Passe les sorties de l\'état "En cours" à l\'état "Terminée" quand la "dateHeureDebut" additionnée de "duree" est atteinte.',
)]
class FinishSortiesCommand extends Command
{
    public function __construct(private SortieManager $sortieManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
       $nbSortiesTerminées = $this->sortieManager->finishSorties();

       $output->writeln($nbSortiesTerminées.' sorties sont terminées.');
       return Command::SUCCESS;
    }
}