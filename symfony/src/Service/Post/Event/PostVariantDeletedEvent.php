<?php

namespace App\Service\Post\Event;

use App\Entity\PostVariant;

readonly class PostVariantDeletedEvent
{
    public function __construct(public PostVariant $variant) {}
}
