<?php

namespace App\Service\Tag\Event;

use App\Entity\Tag;

readonly class TagUpdatedEvent
{
    public function __construct(
        public Tag $tag,
        public Tag $tagOld,
    ) {}
}
