<?php

namespace App\Api\Console\Object;

use App\Entity\NavigationVariant;

class NavigationVariantObject
{
    public int $language_id;
    public ?string $name;

    public function __construct(NavigationVariant $variant)
    {
        $this->language_id = $variant->getLanguageId();
        $this->name = $variant->getName();
    }
}
