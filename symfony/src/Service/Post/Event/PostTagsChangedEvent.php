<?php

namespace App\Service\Post\Event;

use App\Entity\Post;
use App\Entity\Tag;

class PostTagsChangedEvent
{

    public function __construct(
        public Post $post,
        /**
         * @var Tag[]
         */
        public array $oldTags,

        /**
         * @var Tag[]
         */
        public array $newTags
    ) {}


}
