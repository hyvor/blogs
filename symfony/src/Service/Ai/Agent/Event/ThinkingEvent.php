<?php

namespace App\Service\Ai\Agent\Event;

/**
 * A chunk of the assistant's reasoning. Sent to the frontend for every delta; only the
 * completed block's summary (see ThinkingComplete) is persisted, as an AiMessageThinking row.
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
