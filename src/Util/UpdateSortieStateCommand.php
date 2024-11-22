<?php

namespace App\Util;

use App\Service\SortieStateUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;

class UpdateSortieStateCommand extends Command
{
    public function __construct(private readonly SortieStateUpdater $stateUpdater)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->stateUpdater->updateEtats();
        return Command::SUCCESS;
    }
}