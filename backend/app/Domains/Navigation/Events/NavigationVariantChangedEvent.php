<?php

namespace App\Domains\Navigation\Events;

use App\Models\NavigationVariant;
use Illuminate\Foundation\Events\Dispatchable;

class NavigationVariantChangedEvent
{
    use Dispatchable;

    public function __construct(public NavigationVariant $variant)
    {
    }
}
