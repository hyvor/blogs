<?php

namespace App\Service\Blog\Event;

use App\Entity\BlogVariant;

readonly class BlogVariantUpdatedEvent
{
    public function __construct(
        public BlogVariant $variant,
        public BlogVariant $variantOld,
    ) {}
}
