<?php

namespace App\Service\Theme\Event;

use App\Entity\Blog;

readonly class AssetEditedEvent
{
    public function __construct(public Blog $blog, public string $name) {}
}
