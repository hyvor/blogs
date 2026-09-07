<?php

namespace App\Service\Media\Event;

use App\Entity\Media;

readonly class MediaDeletedEvent
{
    public function __construct(public Media $media) {}
}
