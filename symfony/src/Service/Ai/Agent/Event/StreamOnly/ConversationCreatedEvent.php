<?php

namespace App\Service\Ai\Agent\Event\StreamOnly;

use App\Service\Ai\Agent\Event\AgentEvent;

readonly class ConversationCreatedEvent implements AgentEvent
{
    public function __construct(
        public int $conversationId,
        public ?string $title,
    ) {
    }

    public function getType(): string
    {
        return 'conversation_created';
    }

    public function getPayload(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'title' => $this->title,
        ];
    }
}
