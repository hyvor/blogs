<?php

namespace App\Service\Blog\Event;

use App\Entity\Blog;

readonly class BlogCreatedEvent
{

    public function __construct(
        public Blog $blog,
    ) {}

}
