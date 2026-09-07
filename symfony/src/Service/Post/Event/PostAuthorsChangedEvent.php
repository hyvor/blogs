<?php

namespace App\Service\Post\Event;

use App\Entity\Post;
use App\Entity\User;

readonly class PostAuthorsChangedEvent
{

    public function __construct(
        public Post $post,
        /**
         * @var User[]
         */
        public array $oldAuthors,

        /**
         * @var User[]
         */
        public array $newAuthors
    ) {}

}
