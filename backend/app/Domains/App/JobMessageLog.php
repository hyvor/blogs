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

    public function info($message) : void
    {
        $this->handleMessage('info', $message);
    }

    public function warning($message) : void
    {
        $this->handleMessage('warning', $message);
    }

    public function error($message)   : void
    {
        $this->handleMessage('error', $message);
    }

    private function handleMessage($type, $message) : void
    {
        if ($this->command) {
            $this->outputToConsole($type, $message);
        } else {
            $this->storeMessage($type, $message);
        }
    }

    private function outputToConsole($type, $message) : void
    {
        $message = '[' . now()->format('Y-m-d H:i:s') . '] ' . $message;
        $this->command->{$type}($message);
    }

    private function storeMessage($type, $message) : void
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