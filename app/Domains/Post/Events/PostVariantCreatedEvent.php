<?php

namespace App\Domains\Post\Events;

use App\Models\PostVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostVariantCreatedEvent
{

    use Dispatchable, SerializesModels;

    public PostVariant $variant;

    public function __construct(PostVariant $variant)
    {
        $this->variant = $variant;
    }

}