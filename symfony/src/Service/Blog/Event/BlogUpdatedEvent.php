<?php

namespace App\Service\Blog\Event;

use App\Entity\Blog;

readonly class BlogUpdatedEvent
{
    public function __construct(
        public Blog $blog,
        public Blog $blogOld,
    ) {}
}
