<?php declare(strict_types=1);

namespace App\Service\Post\Content\Marks;

use Hyvor\Phrosemirror\Types\MarkType;

/**
 * Wraps inline content that carries a pending suggestion (insert/delete/format) or a
 * comment thread, mirroring @hyvor/richtext's marks.suggestion (schema.ts). Who made the
 * suggestion/comment and its reply thread are never stored in the document - only
 * `type`/`id` (and, for "format", `add`/`remove`) round-trip through the JSON. See
 * App\Service\Post\Suggestion\PostSuggestionService, which is the backing store keyed by
 * `id` (the same "editor only holds a reference, host owns the real data" split the
 * richtext package documents for its `source` config).
 *
 * No fromHtml() override on purpose (defaults to no parse rules): pasting HTML from
 * elsewhere shouldn't silently attach content to an existing suggestion/thread just
 * because it happens to carry the same data attribute.
 */
class Suggestion extends MarkType
{
    public string $name = 'suggestion';
    public string $attrs = SuggestionAttrs::class;
}
