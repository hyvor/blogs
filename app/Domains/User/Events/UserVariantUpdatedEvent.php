<?php

namespace App\Domains\User\Events;

use App\Models\UserVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserVariantUpdatedEvent
{
    use Dispatchable;
    use SerializesModels;

    public UserVariant $variant;
    public UserVariant $variantOld;

    public function __construct(UserVariant $variant)
    {
        $this->variant = $variant;
        $this->variantOld = new UserVariant($variant->getOriginal());
    }
}
