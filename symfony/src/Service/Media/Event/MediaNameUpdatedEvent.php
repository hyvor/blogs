<?php

namespace App\Service\Media\Event;

use App\Entity\Media;

readonly class MediaNameUpdatedEvent {

    public function __construct(
        public Media $media,
        public string $oldUrl,
        public string $newUrl
    ) {}

}
