<?php

namespace App\Service\Import;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Reports progress messages from an import parser: to the console when run
 * interactively, or stored in-memory otherwise.
 */
class ImportLog
{
    /** @var array<int, array{type: string, message: string}> */
    private array $messages = [];

    public function __construct(private ?SymfonyStyle $io = null)
    {
    }

    public function info(string $message): void
    {
        $this->handleMessage('info', $message);
    }

    public function warn(string $message): void
    {
        $this->handleMessage('warn', $message);
    }

    public function error(string $message): void
    {
        $this->handleMessage('error', $message);
    }

    private function handleMessage(string $type, string $message): void
    {
        if ($this->io !== null) {
            match ($type) {
                'warn' => $this->io->warning($message),
                'error' => $this->io->error($message),
                default => $this->io->text($message),
            };
        } else {
            $this->messages[] = ['type' => $type, 'message' => $message];
        }
    }

    /**
     * @return array<int, array{type: string, message: string}>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
