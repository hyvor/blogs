<?php

namespace App\Domains\Theme\Events;

use App\Models\Blog;
use Illuminate\Foundation\Events\Dispatchable;

class StylesEditedEvent
{
    use Dispatchable;

    public function __construct(public Blog $blog)
    {
    }
}
