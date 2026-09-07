<?php

namespace App\Service\Cache\Event;

use App\Entity\Blog;

readonly class CacheClearTemplatesEvent
{
    public function __construct(public Blog $blog) {}
}
