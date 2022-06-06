<?php

namespace App\Domains\User\Events;

use App\Models\UserVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserVariantDeletedEvent
{

    use Dispatchable, SerializesModels;

    public UserVariant $variant;

    public function __construct(UserVariant $variant)
    {
        $this->variant = $variant;
    }

}