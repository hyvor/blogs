<?php

namespace App\Service\Navigation\Event;

use App\Entity\Navigation;

readonly class NavigationChangedEvent
{
    public function __construct(public Navigation $navigation) {}
}
