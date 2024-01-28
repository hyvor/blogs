<?php

namespace App\Domains\Blog\Events;

use App\Models\BlogVariant;
use Illuminate\Foundation\Events\Dispatchable;

class BlogVariantUpdatedEvent
{
    use Dispatchable;

    public function __construct(public BlogVariant $variant)
    {
    }
}
