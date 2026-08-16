<?php

namespace App\Service\LinkAnalysis;

use App\Entity\PostVariant;
use App\Service\Post\PostService;

/**
 * Maintains the `link_analysis` status cache stored on a PostVariant, used to
 * quickly render link statuses without a LinkAnalyzerLink query.
 */
class PostVariantLinkStatusCacheService
{
    public function __construct(
        private PostService $postService,
    ) {}

    /**
     * @param array<string, number> $results
     */
    public function update(
        PostVariant $variant,
        array $results,
        bool $append = false
    ): void
    {
        $currentVariantResult = $variant->getLinkAnalysis() ?? [];
        $blog = $variant->getPost()->getBlog();

        $this->postService->updatePostVariant(
            $variant,
            $blog,
            [
                'link_analysis' => $append ? array_merge(
                    $currentVariantResult,
                    $results
                ) : $results
            ]
        );
    }
}
