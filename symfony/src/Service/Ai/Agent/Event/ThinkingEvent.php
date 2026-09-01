<?php

namespace App\Service\Ai\Agent\Event;

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
