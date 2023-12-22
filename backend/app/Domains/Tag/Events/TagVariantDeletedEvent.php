<?php

namespace App\Domains\Tag\Events;

use App\Models\TagVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagVariantDeletedEvent
{
    use Dispatchable;
    use SerializesModels;

    public TagVariant $variant;

    public function __construct(TagVariant $variant)
    {
        $this->variant = $variant;
    }
}
