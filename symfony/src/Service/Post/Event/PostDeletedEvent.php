<?php

namespace App\Service\Post\Event;

use App\Entity\Post;

readonly class PostDeletedEvent
{
    public function __construct(public Post $post) {}
}
