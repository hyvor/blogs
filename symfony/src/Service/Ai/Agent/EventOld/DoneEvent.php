<?php

namespace App\Service\Ai\Agent\EventOld;

/**
 * @deprecated
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
