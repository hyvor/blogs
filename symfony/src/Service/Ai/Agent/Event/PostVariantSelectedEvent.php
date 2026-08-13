<?php

namespace App\Service\Ai\Agent\Event;

use App\Api\Console\Object\PostVariantObject;

/**
 * Signals which post variant the agent picked to work on. Sent to the frontend only —
 * not part of the ai_conversations/ai_messages/ai_message_chunks model.
 */
readonly class PostVariantSelectedEvent implements AgentEvent
{
    public function __construct(public PostVariantObject $postVariant)
    {
    }

    public function getType(): string
    {
        return 'post_variant';
    }

    public function getPayload(): array
    {
        return ['post_variant' => $this->postVariant];
    }
}
