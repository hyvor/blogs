<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\Media;
use App\Service\Route\PermalinkService;

class MediaObjectFactory
{
    public function __construct(private PermalinkService $permalinkService) {}

    public function create(Media $media, Blog $blog): MediaObject
    {
        return new MediaObject(
            $media->getId(),
            $media->getPostId(),
            $media->getCreatedAt()->getTimestamp(),
            $this->permalinkService->getMediaPermalink($media, $blog),
            $media->getName(),
            $media->getOriginalName(),
            $media->getExtension(),
        );
    }
}
