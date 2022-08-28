<?php

namespace App\Domains\Route\Events;

use App\Models\Redirect;
use App\Models\Route;
use Illuminate\Foundation\Events\Dispatchable;

class RouteChangedEvent
{

    use Dispatchable;

    public function __construct(public Route $route) {}

}