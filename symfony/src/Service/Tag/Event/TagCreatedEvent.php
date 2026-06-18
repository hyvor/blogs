<?php

namespace App\Service\Tag\Event;

use App\Entity\Tag;

readonly class TagCreatedEvent
{
    public function __construct(public Tag $tag) {}
}
