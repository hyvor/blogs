<?php

namespace App\Service\Tag\Event;

use App\Entity\TagVariant;

readonly class TagVariantCreatedEvent
{
    public function __construct(public TagVariant $variant) {}
}
