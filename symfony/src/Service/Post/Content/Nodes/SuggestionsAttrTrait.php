<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

/**
 * Adds the `suggestions` attr that every non-doc, non-text node carries, mirroring
 * @hyvor/richtext's schema.ts (withSuggestionAttrs). A list of {type, id} entries
 * (plus, for type "format", an `oldAttrs` snapshot) recording whole-node pending
 * suggestions/comment threads - independent of any `suggestion` marks the node's own
 * inline content may carry. Null (not []) when there is nothing pending.
 *
 * @see \App\Service\Post\Content\Marks\Suggestion
 */
trait SuggestionsAttrTrait
{
    /** @var array<int, array<string, mixed>>|null */
    public ?array $suggestions = null;
}
