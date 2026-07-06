<?php

namespace App\Service\Blog\Event;

use App\Entity\Blog;

readonly class BlogDeletedEvent
{
    public function __construct(
        public Blog $blog,
    ) {}
}
