<?php

namespace App\Service\Ai\Agent\Event;

/**
 * Signals that the agent has started calling a tool. Sent to the frontend only — the tool's
 * arguments aren't known yet at this point, so there's nothing meaningful to persist here;
 * the completed call (see ToolCallComplete) is what's saved, as an AiMessageToolCall row.
 */
readonly class ToolCallStartedEvent implements AgentEvent
{
    public function __construct(public string $tool)
    {
    }

    public function getType(): string
    {
        return 'tool_call';
    }

    public function getPayload(): array
    {
        return ['tool' => $this->tool];
    }
}
