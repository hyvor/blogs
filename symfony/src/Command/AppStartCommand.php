<?php

namespace App\Command;

use App\Entity\Theme;
use App\Service\Theme\RepoSync\Message\RepoSyncMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:start',
    description: 'Runs startup tasks and starts the FrankenPHP server',
)]
class AppStartCommand
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
    ) {}

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->syncThemesIfEmpty($io);

        $process = new Process([
            'frankenphp',
            'run',
            '--config',
            '/etc/caddy/Caddyfile',
        ]);

        $io->note('Starting FrankenPHP...');

        $process->setTimeout(null);
        $process->run(function (string $type, string $buffer): void {
            if ($type === Process::OUT) {
                echo $buffer;
            } else {
                fwrite(STDERR, $buffer);
            }
        });

        return Command::SUCCESS;
    }

    public function syncThemesIfEmpty(?SymfonyStyle $io = null): void
    {
        $count = $this->em->getRepository(Theme::class)->count([]);

        if ($count > 0) {
            return;
        }

        $io?->note('No themes found in the database. Dispatching job to sync themes...');

        try {
            $this->bus->dispatch(new RepoSyncMessage());
        } catch (\Throwable $e) {
            $io?->warning('Failed to dispatch job to sync themes (skipping). Error: ' . $e->getMessage());
        }
    }
}
