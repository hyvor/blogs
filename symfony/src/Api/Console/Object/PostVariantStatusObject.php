<?php

namespace App\Api\Console\Object;

use App\Entity\PostVariant;

class PostVariantStatusObject
{
    public int $language_id;
    public string $status;

    public function __construct(PostVariant $variant)
    {
        $this->language_id = $variant->getLanguage()->getId();
        $this->status = $variant->getStatus()->value;
    }
}
