<?php

namespace App\Service\Ai\Agent\Event;

/**
 * A chunk of the assistant's reasoning. Sent to the frontend for every delta; persisted
 * as an `ai_message_chunks` row of type 'thinking' (consecutive deltas are merged into a
 * single row rather than saved one-by-one).
 */
readonly class ThinkingEvent implements AgentEvent
{
    public function __construct(public string $content)
    {
    }

    public function getType(): string
    {
        return 'thinking';
    }

    public function getPayload(): array
    {
        return ['content' => $this->content];
    }
}
