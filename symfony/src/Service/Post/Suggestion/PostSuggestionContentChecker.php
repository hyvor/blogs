<?php declare(strict_types=1);

namespace App\Service\Post\Suggestion;

/**
 * Publishing with pending suggestions is a bad idea:
 * - <del> are not supposed to be in content
 * - even <ins> and <format> types are buggy to have.
 * hence, it's better to force the user to resolve suggestions before publishing.
 *
 * Checks whether the given Document contains any pending suggestions
 * Does not check for comments, only suggestions (insert, delete, format)
 * Deliberately walks the decoded JSON directly instead of going through PostSchema
 */
class PostSuggestionContentChecker
{


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
            if (is_array($suggestions) && $this->hasNonCommentSuggestions($suggestions) > 0) {
                return true;
            }
        }

        $marks = $node['marks'] ?? null;
        if (is_array($marks)) {
            foreach ($marks as $mark) {
                if (
                    is_array($mark) &&
                    ($mark['type'] ?? null) === 'suggestion' &&
                    is_array($mark['attrs'] ?? null) &&
                    ($mark['attrs']['type'] ?? null) !== 'comment'
                ) {
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

    /**
     * @param array<mixed> $suggestionsAttrValue
     */
    private function hasNonCommentSuggestions(array $suggestionsAttrValue): bool
    {
        return count(
            array_filter(
                $suggestionsAttrValue,
                fn ($suggestion) => is_array($suggestion) && ($suggestion['type'] ?? null) !== 'comment'
            )
        ) > 0;
    }
}
