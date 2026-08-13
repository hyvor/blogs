<?php

namespace App\Service\Ai\Agent\Event;

/**
 * A chunk of the assistant's final text response. Sent to the frontend for every delta;
 * persisted as an `ai_message_chunks` row of type 'text' (consecutive deltas are merged
 * into a single row rather than saved one-by-one).
 */
readonly class TextEvent implements AgentEvent
{
    public function __construct(public string $content)
    {
    }

    public function getType(): string
    {
        return 'text';
    }

    public function getPayload(): array
    {
        return ['content' => $this->content];
    }
}
