<?php

namespace App\Command;

use App\Manager\SortieManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
#[AsCommand(
    name: 'sortie:demarre',
    description: 'Passe les sorties de l\'état "Clôturée" à l\'état "En cours" quand la "dateHeureDebut" est atteinte.',
)]
class BeginSortiesCommand extends Command
{
    public function __construct(private SortieManager $sortieManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
       $nbSortiesEnCours = $this->sortieManager->beginSorties();

       $output->writeln($nbSortiesEnCours.' sorties sont en cours.');
       return Command::SUCCESS;
    }
}