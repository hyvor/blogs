<?php

namespace App\Service\Post\Event;

use App\Entity\PostVariant;

readonly class PostVariantPublishedEvent
{
    public function __construct(public PostVariant $variant) {}
}
