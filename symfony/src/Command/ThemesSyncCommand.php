<?php

namespace App\Command;

use App\Service\Theme\RepoSync\RepoSyncMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'themes:sync',
    description: 'Download and sync themes from the GitHub repository',
)]
class ThemesSyncCommand extends Command
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    /** @throws \Symfony\Component\Messenger\Exception\ExceptionInterface */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->bus->dispatch(new RepoSyncMessage());
        $io->success('Theme sync job dispatched.');
        return Command::SUCCESS;
    }
}
