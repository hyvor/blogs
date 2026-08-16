<?php

namespace App\Api\Console\Object;

use App\Entity\PostSuggestionReply;

class PostSuggestionReplyObject
{
    public string $id;
    public string $author;
    public string $content;
    // milliseconds since epoch, matching @hyvor/richtext's SuggestionReply.timestamp (Date.now())
    public int $timestamp;

    public function __construct(PostSuggestionReply $reply)
    {
        $this->id = $reply->getId();
        $this->author = 'user:' . $reply->getAuthorUserId();
        $this->content = $reply->getContent();
        $this->timestamp = $reply->getCreatedAt()->getTimestamp() * 1000;
    }
}
