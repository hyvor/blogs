<?php

namespace App\Service\Post\Event;

use App\Entity\PostVariant;

readonly class PostVariantCreatedEvent
{
    public function __construct(public PostVariant $variant) {}
}
