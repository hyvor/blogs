<?php

namespace App\Api\Console\Object;

use App\Entity\PostSuggestion;
use App\Entity\PostSuggestionReply;

/**
 * Matches @hyvor/richtext's SuggestionSourceEntry ({author, comments}), plus `id` -
 * used both as a single-entity response (create/reply) and, keyed by id, as the map
 * `source.get()` expects back.
 */
class PostSuggestionObject
{
    public string $id;
    public string $author;
    /** @var PostSuggestionReplyObject[] */
    public array $comments;

    public function __construct(PostSuggestion $suggestion)
    {
        $this->id = $suggestion->getId();
        $this->author = 'user:' . $suggestion->getAuthorUserId();
        $this->comments = array_map(
            fn(PostSuggestionReply $reply) => new PostSuggestionReplyObject($reply),
            $suggestion->getReplies()->toArray(),
        );
    }
}
