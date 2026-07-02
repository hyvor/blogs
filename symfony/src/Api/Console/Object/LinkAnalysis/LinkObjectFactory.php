<?php

namespace App\Api\Console\Object\LinkAnalysis;

use App\Entity\Blog;
use App\Entity\Enum\LinkAnalyzerLinkStatus;
use App\Entity\LinkAnalyzerLink;
use App\Service\Route\PermalinkService;

class LinkObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService
    ) {}

    public function create(Blog $blog, LinkAnalyzerLink $link): LinkObject
    {
        $postVariant = $link->getPostVariant();
        $status = $link->isIgnore()
            ? LinkAnalyzerLinkStatus::IGNORED
            : LinkAnalyzerLinkStatus::fromStatus($link->getStatusCode());

        return new LinkObject(
            id: $link->getId(),
            url: $link->getUrl(),
            full_url: $link->getFullUrl(),
            status_code: $link->getStatusCode(),
            status_type: $status,
            ignored: $link->isIgnore(),
            ignore_reason: $link->getIgnoreReason(),
            comment: $link->getComment(),
            post_id: $postVariant->getPost()->getId(),
            post_variant_id: $postVariant->getId(),
            post_variant_language_id: $postVariant->getLanguage()->getId(),
            post_variant_title: $postVariant->getTitle(),
            post_variant_url: $this->permalinkService->getPostPermalink(
                $postVariant->getPost(),
                $blog,
                $postVariant->getLanguage(),
            ),
        );
    }
}
