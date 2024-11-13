<?php

namespace App\Command;

use App\Manager\SortieManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
#[AsCommand(
    name: 'sortie:ouverte',
    description: 'Passe les sorties de l\'état "Créée" à l\'état "Ouverte" quand la "dateDebutInscription" est atteinte.',
)]
class PublishSortiesCommand extends Command
{
    public function __construct(private SortieManager $sortieManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
       $nbSortiesOuvertes = $this->sortieManager->openSorties();

       $output->writeln($nbSortiesOuvertes.' sorties ont été ouvertes.');
       return Command::SUCCESS;
    }
}