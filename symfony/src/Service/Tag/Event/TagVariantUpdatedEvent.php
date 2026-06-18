<?php

namespace App\Service\Tag\Event;

use App\Entity\TagVariant;

readonly class TagVariantUpdatedEvent
{
    public function __construct(
        public TagVariant $variant,
        public TagVariant $variantOld,
    ) {}
}
