<?php

namespace App\Service\Tag\Event;

use App\Entity\Tag;

readonly class TagDeletedEvent
{
    public function __construct(public Tag $tag) {}
}
