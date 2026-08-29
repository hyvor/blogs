<?php declare(strict_types=1);

namespace App\Service\Post\Suggestion;

/**
 * Scans a post variant's raw content JSON (before it is parsed into a Document) for any
 * pending track-changes suggestion or open comment thread - a `suggestion` mark instance,
 * or a non-empty `suggestions` node attr (see App\Service\Post\Content\Marks\Suggestion
 * and SuggestionsAttrTrait). Used to block a post from going public (publish, or
 * "Update" on an already-published post) while it still has unresolved suggestions/
 * comments, so raw <ins>/<del>/comment markup can never leak into the public HTML.
 *
 * Deliberately walks the decoded JSON directly instead of going through PostSchema/
 * Document::fromJson - this only needs to answer a yes/no question, not build a full
 * validated document, and must also tolerate content that fails schema validation.
 */
class PostSuggestionContentChecker
{

    /**
     * Checks whether the given Document contains any pending suggestions
     * Does not check for comments, only suggestions (insert, delete, format)
     */
    public function hasPendingSuggestions(?string $json): bool
    {
        if ($json === null || $json === '') {
            return false;
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return false;
        }

        if (!is_array($decoded)) {
            return false;
        }

        return $this->nodeHasPendingSuggestions($decoded);
    }

    /**
     * @param array<mixed, mixed> $node
     */
    private function nodeHasPendingSuggestions(array $node): bool
    {
        $attrs = $node['attrs'] ?? null;
        if (is_array($attrs)) {
            $suggestions = $attrs['suggestions'] ?? null;
            if (is_array($suggestions) && count($suggestions) > 0) {
                return true;
            }
        }

        $marks = $node['marks'] ?? null;
        if (is_array($marks)) {
            foreach ($marks as $mark) {
                if (is_array($mark) && ($mark['type'] ?? null) === 'suggestion') {
                    return true;
                }
            }
        }

        $content = $node['content'] ?? null;
        if (is_array($content)) {
            foreach ($content as $child) {
                if (is_array($child) && $this->nodeHasPendingSuggestions($child)) {
                    return true;
                }
            }
        }

        return false;
    }
}
