<?php

namespace App\Service\Ai\Agent\Event;

/**
 * Carries the final suggested document content once the agent has made edits. Sent to the
 * frontend only — the underlying ops are already captured as AiMessageToolCall rows, so this
 * isn't separately persisted.
 */
readonly class DocumentChangeEvent implements AgentEvent
{
    public function __construct(
        public int $postVariantId,
        public string $content,
    ) {
    }

    public function getType(): string
    {
        return 'document_change';
    }

    public function getPayload(): array
    {
        return [
            'post_variant_id' => $this->postVariantId,
            'content' => $this->content,
        ];
    }
}
