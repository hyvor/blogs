<?php

namespace App\Domains\Tag\Events;

use App\Models\TagVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagVariantUpdatedEvent
{
    use Dispatchable;
    use SerializesModels;

    public TagVariant $variant;
    public TagVariant $variantOld;

    public function __construct(TagVariant $variant)
    {
        $this->variant = $variant;
        $this->variantOld = new TagVariant($variant->getOriginal());
    }
}
