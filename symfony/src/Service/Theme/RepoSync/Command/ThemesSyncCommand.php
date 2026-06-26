<?php

namespace App\Service\Theme\RepoSync\Command;

use App\Service\App\Messenger\MessageTransport;
use App\Service\Theme\RepoSync\Message\RepoSyncMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'themes:sync',
    description: 'Download and sync themes from the hyvor/hyvor-blogs-themes GitHub repository',
)]
class ThemesSyncCommand extends Command
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Syncing themes...');
        $this->bus->dispatch(new RepoSyncMessage(), [MessageTransport::syncStamp()]);
        $io->success('Theme synced successfully.');
        return Command::SUCCESS;
    }
}
