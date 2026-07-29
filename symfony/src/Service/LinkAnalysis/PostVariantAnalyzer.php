<?php

namespace App\Service\LinkAnalysis;


use App\Entity\Blog;
use App\Entity\Enum\LinkAnalyzerCheckType;
use App\Entity\LinkAnalyzerLink;
use App\Entity\PostVariant;
use App\Service\LinkAnalysis\StatusCheck\AnalyzedLink;
use App\Service\LinkAnalysis\StatusCheck\IgnoreReason;
use App\Service\LinkAnalysis\StatusCheck\StatusResult;
use App\Service\Post\Content\Marks\Link;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;
use Hyvor\Phrosemirror\Document\Mark;

class PostVariantAnalyzer
{
    public function __construct(
        private Blog $blog,
        private PermalinkService $permalinkService,
        private RelativeUrlResolver $relativeUrlResolver,
        private LinkStatusCheckService $linkStatusCheckService,
        private PostVariantLinkService $postVariantLinkService,
        private LinkAnalysisService $linkAnalyzeService,
        private PostContentService $postContentService
    )
    {
    }

    /**
     *  Each variant model should have at least the following fields:
     *  - id
     *  - content
     *  - language_id
     *
     * @param PostVariant[] $variants
     * @param array<int, ResolvedUrl[]>|null $urls // indexed by variant ID
     * @return array<int, LinkAnalyzerLink[]> Links indexed by variant ID
     */
    public function analyzeVariants(
        array $variants,
        ?array $urls = null
    ): array
    {
        $resultsByVariant = [];
        // [variantId => [ResolvedUrl, ResolvedUrl, ...]]
        $variantIndexedUrls = $urls ?? $this->getUrlsFromVariants($variants);
        $allFinalUrls = array_map(
            fn($urls) => array_map(fn($url) => $url->fullUrl, $urls),
            $variantIndexedUrls
        );

        // check HTTP statuses
        $statuses = $this->linkStatusCheckService->check(
            array_values(array_unique(array_merge(...$allFinalUrls))),
            $this->blog,
        );

        foreach ($variants as $variant) {
            if (array_key_exists($variant->getId(), $variantIndexedUrls) === false) {
                continue;
            }

            $urls = $variantIndexedUrls[$variant->getId()];

            // combine the results with the urls
            $results = array_map(
                fn(ResolvedUrl $url) => new AnalyzedLink(
                    $url->originalUrl,
                    $url->fullUrl,
                    $statuses[$url->fullUrl] ?? new StatusResult(
                        LinkAnalyzerCheckType::EXTERNAL,
                        0,
                        ignored: true,
                        ignoreReason: IgnoreReason::INTERNAL_ERROR,
                        comment: 'No status found'
                    ),
                ),
                $urls
            );

            $resultsByVariant[$variant->getId()] = $this->finalizeVariant($variant, $results);
        }

        return $resultsByVariant;
    }

    /**
     * @param array<string>|null $urls
     * @return LinkAnalyzerLink[]
     */
    public function analyzeVariant(
        PostVariant $variant,
        ?array $urls = null,
    ): array {
        $variantUrl = $this->permalinkService->getPostVariantPermalink($variant);

        // TODO: update links on LinkUpdateEvent

        $resolvedUrls = null;

        if ($urls) {
            $resolvedUrls = array_map(fn(string $url) => $this->resolveUrl($url, $variantUrl), $urls);
            $resolvedUrls = array_filter($resolvedUrls);
            $resolvedUrls = [$variant->getId() => $resolvedUrls];
        }

        $resultsByVariant = $this->analyzeVariants([$variant], $resolvedUrls);

        return $resultsByVariant[$variant->getId()] ?? [];
    }

    private function resolveUrl(string $originalUrl, string $variantUrl): ?ResolvedUrl
    {
        if (!$originalUrl) {
            return null;
        }

        if (mb_strlen($originalUrl) > 255) {
            return null;
        }

        // ignore anchor links
        if (str_starts_with($originalUrl, '#')) {
            return null;
        }

        $fullUrl = $this->relativeUrlResolver->resolve($originalUrl, $variantUrl);

        if (!$fullUrl) {
            return null;
        }

        return new ResolvedUrl($originalUrl, $fullUrl);
    }

    /**
     * @param PostVariant[] $variants
     * @return array<int, ResolvedUrl[]> // indexed by variant ID
     */
    private function getUrlsFromVariants(array $variants): array
    {
        $variantIndexedUrls = [];

        foreach ($variants as $variant) {
            $urls = [];

            $content = $variant->getContent();
            if (!$content) {
                continue;
            }

            $doc = $this->postContentService->getDocumentFromJson($content);
            $linkMarks = $doc->getMarks(Link::class);
            $variantUrl = $this->permalinkService->getPostVariantPermalink($variant);

            foreach ($linkMarks as $linkMark) {
                $originalUrl = $this->getHrefFromLinkMark($linkMark);

                if ($originalUrl === null) {
                    continue;
                }

                $resolvedUrl = $this->resolveUrl($originalUrl, $variantUrl);

                if ($resolvedUrl === null) {
                    continue;
                }

                $urls[] = $resolvedUrl;
            }

            if (count($urls) === 0) {
                continue;
            }

            $variantIndexedUrls[$variant->getId()] = $urls;
        }

        return $variantIndexedUrls;
    }

    /**
     * @param AnalyzedLink[] $results
     * @return LinkAnalyzerLink[]
     */
    private function finalizeVariant(PostVariant $variant, array $results): array
    {
        $ignoredLinksUrls = $this->postVariantLinkService->getIgnoredLinks($variant);

        $links = $this->postVariantLinkService->updateLinksFromResults(
            $this->blog,
            $variant,
            $results,
            shouldClear: true,
            ignoreUrls: $ignoredLinksUrls
        );

        // TODO: handle OnLinkUpdateEvent

        $this->postVariantLinkService->updatePostVariantCache(
            $variant,
            $this->linkAnalyzeService->getIgnoreAwareStatusFromLinks($links)
        );

        return $links;
    }

    private function getHrefFromLinkMark(Mark $linkMark): ?string
    {
        $href = $linkMark->attr('href', false);
        return is_string($href) ? $href : null;
    }
}