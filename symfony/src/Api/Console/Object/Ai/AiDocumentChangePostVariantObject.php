<?php

namespace App\Api\Console\Object\Ai;

use App\Entity\PostVariant;

class AiDocumentChangePostVariantObject
{

    public int $id;
    public ?string $title;
    public string $status;
    public ?int $published_at;
    public int $language_id;

    public function __construct(PostVariant $variant)
    {
        $this->id = $variant->getId();
        $this->title = $variant->getTitle();
        $this->status = $variant->getStatus()->value;
        $this->published_at = $variant->getPublishedAt()?->getTimestamp();
        $this->language_id = $variant->getLanguage()->getId();
    }

}
