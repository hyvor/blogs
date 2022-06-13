<?php

namespace App\Domains\Tag\Events;

use App\Models\TagVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagVariantCreatedEvent
{

    use Dispatchable, SerializesModels;

    public TagVariant $variant;

    public function __construct(TagVariant $variant)
    {
        $this->variant = $variant;
    }

}