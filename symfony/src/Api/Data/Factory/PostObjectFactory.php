<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\PostObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Post;
use App\Service\Route\PermalinkService;

class PostObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
    ) {}

    public function create(
        Blog $blog,
        Post $post,
        Language $language,
    ): PostObject {
        return new PostObject($blog, $post, $language, $this->permalinkService);
    }
}
