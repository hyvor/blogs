<?php

namespace App\Service\Blog\Event;

use App\Entity\Blog;

/**
 * Dispatched when the custom domain of the blog is connected, changed, or removed
 * (i.e., the URL where Hyvor Blogs serves the blog changed)
 */
readonly class BlogHostingChangedEvent
{
    public function __construct(
        public Blog $blog,
    ) {}
}
