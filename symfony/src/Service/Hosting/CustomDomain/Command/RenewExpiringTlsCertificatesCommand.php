<?php

namespace App\Service\Hosting\CustomDomain\Command;

use App\Service\Hosting\CustomDomain\Message\RegenerateExpiringTlsCertificatesMessage;
use App\Service\Hosting\CustomDomain\MessageHandler\RegenerateExpiringTlsCertificatesMessageHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'hosting:renew-expiring-tls-certificates',
    description: 'Renews all auto-managed custom domain TLS certificates expiring within 30 days (same as the daily scheduled task)',
)]
class RenewExpiringTlsCertificatesCommand
{
    public function __construct(
        private MessageBusInterface $bus,
        private RegenerateExpiringTlsCertificatesMessageHandler $handler,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option('run in this process instead of dispatching to the async worker')] bool $sync = false,
    ): int {
        $io = new SymfonyStyle($input, $output);
        $message = new RegenerateExpiringTlsCertificatesMessage();

        if ($sync) {
            $io->writeln('Renewing expiring certificates...');
            ($this->handler)($message);
            $io->success('Done.');
        } else {
            $this->bus->dispatch($message);
            $io->success('Dispatched to the async transport. The worker will process it.');
        }

        return Command::SUCCESS;
    }
}
