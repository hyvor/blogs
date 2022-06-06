<?php

namespace App\Domains\User\Events;

use App\Models\PostVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserVariantCreatedEvent
{

    use Dispatchable, SerializesModels;

    public PostVariant $variant;

    public function __construct(PostVariant $variant)
    {
        $this->variant = $variant;
    }

}