<?php

namespace App\Service\LinkAnalysis;

use App\Entity\Blog;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;

class PostVariantAnalyzerFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
        private RelativeUrlResolver $relativeUrlResolver,
        private LinkStatusCheckService $linkStatusCheckService,
        private PostVariantLinkService $postVariantLinkService,
        private LinkAnalysisService $linkAnalyzeService,
        private PostContentService $postContentService
    ) {}

    public function create(Blog $blog): PostVariantAnalyzer
    {
        return new PostVariantAnalyzer(
            $blog,
            $this->permalinkService,
            $this->relativeUrlResolver,
            $this->linkStatusCheckService,
            $this->postVariantLinkService,
            $this->linkAnalyzeService,
            $this->postContentService
        );
    }
}