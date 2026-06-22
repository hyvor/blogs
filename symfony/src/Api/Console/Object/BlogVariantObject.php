<?php

namespace App\Api\Console\Object;

use App\Entity\BlogVariant;

class BlogVariantObject
{
    public int $id;
    public int $language_id;
    public ?string $name;
    public ?string $description;

    public function __construct(BlogVariant $blogVariant)
    {
        $this->id = $blogVariant->getId();
        $this->language_id = $blogVariant->getLanguage()->getId();
        $this->name = $blogVariant->getName();
        $this->description = $blogVariant->getDescription();
    }
}
