<?php

namespace App\Service\Ai\Agent\Event;

readonly class ThinkingDoneEvent implements AgentEvent
{
    public function __construct(public string $content)
    {
    }

    public function getType(): string
    {
        return 'thinking_done';
    }

    public function getPayload(): array
    {
        return ['content' => $this->content];
    }
}
