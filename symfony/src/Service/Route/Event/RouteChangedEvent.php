<?php

namespace App\Service\Route\Event;

use App\Entity\Route;

readonly class RouteChangedEvent
{
    public function __construct(public Route $route) {}
}
