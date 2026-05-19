<?php

namespace App\Api\Console\Object;

use App\Entity\NavigationVariant;

class NavigationVariantObject
{
    public int $navigation_id;
    public int $language_id;
    public ?string $name;

    public function __construct(NavigationVariant $variant)
    {
        $this->navigation_id = $variant->getNavigationId();
        $this->language_id = $variant->getLanguageId();
        $this->name = $variant->getName();
    }
}
