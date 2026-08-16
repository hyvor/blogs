<?php

namespace App\Service\Ai\Agent\Event;

/**
 * Signals that the agent stream has finished. Sent to the frontend only.
 */
readonly class DoneEvent implements AgentEvent
{
    public function getType(): string
    {
        return 'done';
    }

    public function getPayload(): array
    {
        return [];
    }
}
