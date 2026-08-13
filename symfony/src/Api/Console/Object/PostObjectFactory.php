<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;

class PostObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
        private TagObjectFactory $tagObjectFactory,
        private UserObjectFactory $userObjectFactory,
        private PostService $postService,
        private PostContentService $postContentService,
    ) {}

    public function create(Post $post, Blog $blog): PostObject
    {
        return new PostObject(
            $post,
            $blog,
            $this->tagObjectFactory,
            $this->userObjectFactory,
            $this->postService->getPreviewId($post)
        );
    }

    public function createVariant(PostVariant $variant, Post $post, Blog $blog, bool $setHtml = false): PostVariantObject
    {
        return new PostVariantObject(
            $variant,
            $post,
            $blog,
            $this->permalinkService,
            $setHtml ? $this->postContentService : null,
        );
    }
}
