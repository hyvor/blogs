<?php

namespace App\Domains\App;

use Illuminate\Console\Command;

class JobMessageLog
{

    /** @var array<mixed> */
    private array $messages = [];

    public function __construct(
        private ?Command $command = null
    )
    {}

    public function info(string $message) : void
    {
        $this->handleMessage('info', $message);
    }

    public function warn(string $message) : void
    {
        $this->handleMessage('warn', $message);
    }

    public function error(string $message)   : void
    {
        $this->handleMessage('error', $message);
    }

    private function handleMessage(string $type, string $message) : void
    {
        if ($this->command) {
            $this->outputToConsole($type, $message);
        } else {
            $this->storeMessage($type, $message);
        }
    }

    private function outputToConsole(string $type, string $message) : void
    {
        $message = '[' . now()->format('Y-m-d H:i:s') . '] ' . $message;
        $this->command->{$type}($message);
    }

    private function storeMessage(string $type, string $message) : void
    {
        $this->messages[] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => now(),
        ];
    }

    /**
     * @return string[]
     */
    public function getMessages() : array
    {
        return $this->messages;
    }

    public function clearMessages() : void
    {
        $this->messages = [];
    }

}