<?php

namespace App\Domains\Integrations\HyvorTalk\Event;

use App\Models\Blog;

class GatedContentChangedEvent
{

    public function __construct(
        public Blog $blog,
    ) {}

}