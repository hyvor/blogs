<?php

namespace App\Service\Ai\Agent\Event;

/**
 * A chunk of the assistant's final text response. Sent to the frontend for every delta;
 * the full accumulated text is persisted once, as the assistant AiMessage's content.
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
