<?php

namespace App\Service\Media\Event;

use App\Entity\Media;

readonly class MediaCreatedEvent
{
    public function __construct(public Media $media) {}
}
