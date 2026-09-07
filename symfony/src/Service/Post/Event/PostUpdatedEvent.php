<?php

namespace App\Service\Post\Event;

use App\Entity\Post;

readonly class PostUpdatedEvent
{
    public function __construct(public Post $post) {}
}
