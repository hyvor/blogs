<?php

namespace App\Service\LinkAnalysis;

use App\Entity\Blog;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;

class PostVariantLinkAnalyzerFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
        private RelativeUrlResolver $relativeUrlResolver,
        private LinkStatusChecker $linkStatusChecker,
        private LinkAnalyzerRepository $linkAnalyzerRepository,
        private PostVariantLinkStatusCacheService $postVariantLinkStatusCacheService,
        private PostContentService $postContentService
    ) {}

    public function create(Blog $blog): PostVariantLinkAnalyzer
    {
        return new PostVariantLinkAnalyzer(
            $blog,
            $this->permalinkService,
            $this->relativeUrlResolver,
            $this->linkStatusChecker,
            $this->linkAnalyzerRepository,
            $this->postVariantLinkStatusCacheService,
            $this->postContentService
        );
    }
}
