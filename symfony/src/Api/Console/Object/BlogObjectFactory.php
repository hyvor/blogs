<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use App\Service\Route\PermalinkService;

class BlogObjectFactory
{
    public function __construct(
        private PermalinkService $permalink,
        private HyvorTalkService $hyvorTalkService,
        private HyvorPostService $hyvorPostService,
    ) {}

    public function create(Blog $blog): BlogObject
    {
        return new BlogObject(
            $blog,
            $this->permalink->getBlogUrl($blog),
            $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog) !== null,
            $this->hyvorPostService->getHyvorPostOfBlog($blog) !== null,
        );
    }
}
