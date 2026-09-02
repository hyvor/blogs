<?php

namespace App\Service\Ai\Agent\EventOld;


/**
 * @deprecated
 */
readonly class AgentErrorEvent implements AgentEvent
{
    public function __construct(public string $message)
    {
    }

    public function getType(): string
    {
        return 'error';
    }

    public function getPayload(): array
    {
        return ['message' => $this->message];
    }
}
