<?php

namespace App\Command;

use App\Manager\SortieManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
#[AsCommand(
    name: 'sortie:cloturee',
    description: 'Passe les sorties de l\'état "Ouverte" à l\'état "Clôturée" quand la "dateLimiteInscription" est atteinte.',
)]
class ClotureSortiesCommand extends Command
{
    public function __construct(private SortieManager $sortieManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
       $nbSortiesCloturees = $this->sortieManager->clotureSorties();

       $output->writeln($nbSortiesCloturees.' sorties ont été clôturées.');
       return Command::SUCCESS;
    }
}