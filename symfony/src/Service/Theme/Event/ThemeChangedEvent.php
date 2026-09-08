<?php

namespace App\Service\Theme\Event;

use App\Entity\Blog;

readonly class ThemeChangedEvent
{
    public function __construct(public Blog $blog) {}
}
