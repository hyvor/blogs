<?php

namespace App\Service\Post\Event;

use App\Entity\Post;

readonly class PostCreatedEvent
{
    public function __construct(public Post $post) {}
}
