<?php

namespace App\Service\Ai\Agent\Event;

use Symfony\AI\Platform\Result\ToolCall;

/**
 * Maps a completed tool call to the typed AgentEvent that records it, based on the tool's name.
 * Matching is done by name (not by referencing the tool classes) so this factory doesn't need
 * to know which class implements a given tool.
 */
class ToolCallEventFactory
{
    public function fromToolCall(ToolCall $toolCall): ?AgentEvent
    {
        $arguments = $toolCall->getArguments();
        $rawPostVariantId = $arguments['postVariantId'] ?? 0;
        $postVariantId = is_numeric($rawPostVariantId) ? (int) $rawPostVariantId : 0;

        return match ($toolCall->getName()) {
            'document_get' => new PostVariantReadEvent($postVariantId),
            'document_replace' => new PostVariantEditSuggestedEvent($postVariantId, 'replace', $arguments),
            'document_insert' => new PostVariantEditSuggestedEvent($postVariantId, 'insert', $arguments),
            'document_text_replace' => new PostVariantEditSuggestedEvent($postVariantId, 'replace_text', $arguments),
            'document_delete' => new PostVariantEditSuggestedEvent($postVariantId, 'delete', $arguments),
            'get_tags' => new GetTagsEvent($arguments),
            'get_authors' => new GetAuthorsEvent($arguments),
            'get_post_variants' => new GetPostVariantsEvent($arguments),
            default => null,
        };
    }
}
