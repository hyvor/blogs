<?php

namespace App\Service\Ai\Agent\Event;

/**
 * The agent called the document_get tool to read a post variant's content.
 */
readonly class PostVariantReadEvent implements AgentEvent
{
    public function __construct(
        public int $postVariantId,
    ) {}

    public function getType(): string
    {
        return 'post_variant_read';
    }

    public function getPayload(): array
    {
        return [
            'post_variant_id' => $this->postVariantId,
        ];
    }
}
