<?php

namespace App\Service\Ai\Agent\Event;

/**
 * Tells the frontend which conversation this stream belongs to, so it can send the same
 * conversation_id on the next prompt to continue this conversation. Sent to the frontend
 * only - the conversation itself is what's persisted, not this event.
 */
readonly class ConversationStartedEvent implements AgentEvent
{
    public function __construct(public int $conversationId)
    {
    }

    public function getType(): string
    {
        return 'conversation_started';
    }

    public function getPayload(): array
    {
        return ['conversation_id' => $this->conversationId];
    }
}
