<?php

namespace App\Service\Ai\Agent\Event;

/**
 * Fallback signal sent to the frontend when a tool call completes but ToolCallEventFactory
 * has no specific domain event for it (e.g. a tool added later that isn't mapped yet).
 * Sent to the frontend only - the tool call itself is always persisted as an
 * AiMessageToolCall row regardless of whether it has a mapped domain event.
 */
readonly class ToolCallCompletedEvent implements AgentEvent
{
    public function __construct(public string $tool)
    {
    }

    public function getType(): string
    {
        return 'tool_result';
    }

    public function getPayload(): array
    {
        return ['tool' => $this->tool];
    }
}
