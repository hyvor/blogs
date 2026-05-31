<?php

namespace App\Service\Navigation\Event;

use App\Entity\NavigationVariant;

readonly class NavigationVariantChangedEvent
{
    public function __construct(public NavigationVariant $variant) {}
}
