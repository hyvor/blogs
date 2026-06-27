<?php

namespace App\Api\Console\Object;

use App\Entity\TagVariant;

class TagVariantObject
{
    public int $language_id;
    public string $url;
    public ?string $name;
    public ?string $description;

    public function __construct(TagVariant $variant, string $url)
    {
        $this->language_id = $variant->getLanguage()->getId();
        $this->url = $url;
        $this->name = $variant->getName();
        $this->description = $variant->getDescription();
    }
}
