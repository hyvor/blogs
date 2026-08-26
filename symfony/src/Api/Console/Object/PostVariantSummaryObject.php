<?php

namespace App\Api\Console\Object;

use App\Entity\PostVariant;

class PostVariantSummaryObject
{
    public int $id;
    public int $language_id;
    public string $status;
    public ?int $updated_at;
    public ?int $content_updated_at;
    public ?int $words;

    public function __construct(PostVariant $variant)
    {
        $this->id = $variant->getId();
        $this->language_id = $variant->getLanguage()->getId();
        $this->status = $variant->getStatus()->value;
        $this->updated_at = $variant->getUpdatedAt()?->getTimestamp();
        $this->content_updated_at = $variant->getContentUpdatedAt()?->getTimestamp();
        $this->words = $variant->getWords();
    }
}
