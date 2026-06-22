<?php

namespace App\Api\Console\Object;

use App\Entity\NavigationVariant;

class NavigationVariantObject
{
    public ?string $name;

    public function __construct(NavigationVariant $variant)
    {
        $this->name = $variant->getName();
    }
}
