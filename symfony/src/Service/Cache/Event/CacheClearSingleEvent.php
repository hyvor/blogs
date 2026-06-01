<?php

namespace App\Service\Cache\Event;

use App\Entity\Blog;

readonly class CacheClearSingleEvent
{
    public function __construct(public Blog $blog, public string $path) {}
}
