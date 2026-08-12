<?php

namespace App\Service\Ai\Agent\Event;

interface AgentEvent
{
    public function getType(): string;

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array;
}
