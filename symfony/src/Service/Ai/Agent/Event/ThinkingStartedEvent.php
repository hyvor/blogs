<?php

namespace App\Service\Ai\Agent\Event;

/**
 * Signals that the agent has started reasoning. Sent to the frontend only — never persisted.
 */
readonly class ThinkingStartedEvent implements AgentEvent
{
    public function getType(): string
    {
        return 'thinking_started';
    }

    public function getPayload(): array
    {
        return [];
    }
}
