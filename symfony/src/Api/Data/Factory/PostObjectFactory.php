<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\PostObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Route\PermalinkService;

class PostObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
    ) {}

    public function create(
        Post $post,
        PostVariant $variant,
        Blog $blog,
        Language $language,
    ): PostObject {
        return new PostObject($post, $variant, $blog, $language, $this->permalinkService);
    }
}
