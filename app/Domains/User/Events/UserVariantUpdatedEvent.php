<?php

namespace App\Domains\User\Events;

use App\Models\PostVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserVariantUpdatedEvent
{

    use Dispatchable, SerializesModels;

    public PostVariant $variant;
    public PostVariant $variantOld;

    public function __construct(PostVariant $variant)
    {
        $this->variant = $variant;
        $this->variantOld = new PostVariant($variant->getOriginal());
    }

}