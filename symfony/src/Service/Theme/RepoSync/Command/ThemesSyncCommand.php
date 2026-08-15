<?php

namespace App\Service\Theme\RepoSync\Command;

use App\Service\App\Messenger\MessageTransport;
use App\Service\Theme\RepoSync\Message\RepoSyncMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'themes:sync',
    description: 'Download and sync themes from the hyvor/hyvor-blogs-themes GitHub repository',
)]
class ThemesSyncCommand
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {}

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option('disable seeding preview blogs')] bool $noPreviewBlogs = false,
    ): int
    {
        ini_set('memory_limit', '512M');

        $io = new SymfonyStyle($input, $output);
        $io->note('Syncing themes...');
        $this->bus->dispatch(new RepoSyncMessage(!$noPreviewBlogs), [MessageTransport::syncStamp()]);
        $io->success('Theme synced successfully.');
        return Command::SUCCESS;
    }
}
