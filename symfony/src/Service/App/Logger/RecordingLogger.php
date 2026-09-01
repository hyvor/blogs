<?php

namespace App\Service\App\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * This logger records log messages in memory and forwards them to symfony's normal logger if set
 * This is used in AcmeClient to save logs.
 * If this works well, we can add this to the internal lib later
 */
class RecordingLogger extends AbstractLogger
{

    private array $logs = [];

    public function __construct(
        private ?LoggerInterface $logger = null,
    ) {}


    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => (string)$message,
            'context' => $context,
        ];

        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getLogsAsString(bool $withContext = true): string
    {
        $logs = array_map(function ($log) use ($withContext) {
            $context = $withContext && !empty($log['context']) ? ' ' . json_encode($log['context']) : '';
            return strtoupper($log['level']) . ': ' . $log['message'] . $context;
        }, $this->logs);

        return implode("\n", $logs);
    }

}
