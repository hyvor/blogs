<?php

namespace App\Domains\Navigation\Events;

use App\Models\Navigation;
use Illuminate\Foundation\Events\Dispatchable;

class NavigationChangedEvent
{
    use Dispatchable;

    public function __construct(public Navigation $navigation) {}
}