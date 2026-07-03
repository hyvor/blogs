<?php

namespace App\Service\Blog\Event;

use App\Entity\HostingChanges;

readonly class BlogHostingChangedEvent
{
    public function __construct(
        public HostingChanges $hostingChange,
    ) {}
}
