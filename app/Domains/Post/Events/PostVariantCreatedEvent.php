<?php

namespace App\Domains\Post\Events;

use App\Models\PostVariant;

class PostVariantCreatedEvent
{

    public PostVariant $variant;

    public function __construct(PostVariant $variant)
    {
        $this->variant = $variant;
    }

}