<?php

namespace App\Api\Console\Object\Ai;

use App\Entity\PostVariant;

/**
 * Summary of a post variant referenced by a document_change event somewhere in a conversation -
 * enough for the frontend to show what was edited without a separate lookup per post.
 */
class AiConversationPostVariantObject
{

    public int $id;
    public ?string $title;
    public string $status;
    public ?int $published_at;

    public function __construct(PostVariant $variant)
    {
        $this->id = $variant->getId();
        $this->title = $variant->getTitle();
        $this->status = $variant->getStatus()->value;
        $this->published_at = $variant->getPublishedAt()?->getTimestamp();
    }

}
