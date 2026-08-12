<?php

namespace App\Service\Ai\Agent\Event;

/**
 * The agent called one of the document_replace, document_insert, document_text_replace,
 * or document_delete tools to suggest an edit to a post variant.
 */
readonly class PostVariantEditSuggestedEvent implements AgentEvent
{
    /**
     * @param array<string, mixed> $arguments raw tool call arguments
     */
    public function __construct(
        public int $postVariantId,
        public string $operation,
        public array $arguments,
    ) {}

    public function getType(): string
    {
        return 'post_variant_edit_suggested';
    }

    public function getPayload(): array
    {
        return [
            'post_variant_id' => $this->postVariantId,
            'operation' => $this->operation,
            'arguments' => $this->arguments,
        ];
    }
}
