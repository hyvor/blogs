<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;

class PostObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
        private TagObjectFactory $tagObjectFactory,
        private UserObjectFactory $userObjectFactory,
        private PostContentService $postContentService,
    ) {}

    public function create(Post $post, Blog $blog, bool $setHtml = false): PostObject
    {
        return new PostObject(
            $post,
            $blog,
            $this->permalinkService,
            $this->tagObjectFactory,
            $this->userObjectFactory,
            $setHtml ? $this->postContentService : null,
        );
    }

    public function createVariant(PostVariant $variant, Post $post, Blog $blog): PostVariantObject
    {
        return new PostVariantObject($variant, $post, $blog, $this->permalinkService);
    }
}
