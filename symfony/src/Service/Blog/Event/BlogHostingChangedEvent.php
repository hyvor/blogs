<?php

namespace App\Service\Blog\Event;

use App\Entity\HostingChange;

readonly class BlogHostingChangedEvent
{
    public function __construct(
        public HostingChange $hostingChange,
    ) {}
}
