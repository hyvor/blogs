<?php

namespace App\Service\Theme\Event;

use App\Entity\Blog;

readonly class StylesEditedEvent
{
    public function __construct(public Blog $blog) {}
}
