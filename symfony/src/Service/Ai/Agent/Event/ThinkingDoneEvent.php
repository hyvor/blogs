<?php

namespace App\Service\Ai\Agent\Event;

readonly class ThinkingDoneEvent implements AgentEvent
{
    public function getType(): string
    {
        return 'thinking_done';
    }

    public function getPayload(): array
    {
        return [];
    }
}
