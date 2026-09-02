<?php

namespace App\Service\Ai\Agent\EventOld;

/**
 * @deprecated
 */
interface AgentEvent
{
    public function getType(): string;

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array;
}
