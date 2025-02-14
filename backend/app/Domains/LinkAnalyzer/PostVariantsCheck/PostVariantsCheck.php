<?php

namespace App\Domains\LinkAnalyzer\PostVariantsCheck;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\AnalyzedLinkDto;
use App\Domains\LinkAnalyzer\Check\ResolvedUrl;
use App\Domains\LinkAnalyzer\LinkAnalyzeService;
use App\Domains\LinkAnalyzer\LinkStatusCheck\LinkStatusCheckService;
use App\Domains\LinkAnalyzer\PostVariantLinkService;
use App\Domains\LinkAnalyzer\RelativeUrlResolver;
use App\Domains\Post\Content\Marks\Link;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\PostVariant;
use Hyvor\Phrosemirror\Document\Mark;
use Illuminate\Database\Eloquent\Collection;

class PostVariantsCheck
{

    /**
     * @var Collection<int, Language>
     */
    private Collection $languages;

    public function __construct(private readonly Blog $blog)
    {
        $this->languages = $blog->languages;
    }

    /**
     * Each variant model should have at least the following fields:
     *
     * - id
     * - content
     * - language_id
     *
     * @param iterable<PostVariant> $variants
     */
    public function check(iterable $variants): void
    {
        // [variantId => [ResolvedUrl, ResolvedUrl, ...]]
        $variantIndexedUrls = $this->getUrlsFromVariants($variants);
        $allFinalUrls = $variantIndexedUrls->map(fn($urls) => array_map(
            fn($url) => $url->fullUrl,
            $urls
        ))->toArray();
        $allFinalUrls = array_merge(...$allFinalUrls);

        // check HTTP statuses
        $statusCheck = app(LinkStatusCheckService::class);
        $statuses = $statusCheck->check($allFinalUrls, $this->blog);

        foreach ($variants as $variant) {
            if ($variantIndexedUrls->has($variant->id) === false) {
                continue;
            }

            $urls = $variantIndexedUrls[$variant->id] ?? [];

            // combine the results with the urls
            $results = array_map(
                fn(ResolvedUrl $url) => new AnalyzedLinkDto(
                    $url->originalUrl,
                    $url->fullUrl,
                    $statuses[$url->fullUrl] ?? 500,
                ),
                $urls
            );

            $this->finalizeVariant($variant, $results);
        }
    }

    /**
     * @param iterable<PostVariant> $variants
     * @return \Illuminate\Support\Collection<int, array<int, ResolvedUrl>>
     */
    private function getUrlsFromVariants(iterable $variants)
    {
        $variantIndexedUrls = collect();

        foreach ($variants as $variant) {
            $urls = [];

            $content = $variant->content;
            if (!$content) {
                continue;
            }

            $language = $this->findLanguageById($variant->language_id);
            if (!$language) {
                continue;
            }

            event(new OnStartEvent($variant));

            $doc = PostContentService::getDocumentFromJson($content, $this->blog);
            $linkMarks = $doc->getMarks(Link::class);
            $variantUrl = PermalinkRepository::getPostVariantPermalink($this->blog, $variant, $language);

            foreach ($linkMarks as $linkMark) {
                $originalUrl = $this->getHrefFromLinkMark($linkMark);

                if (!$originalUrl) {
                    continue;
                }

                if (mb_strlen($originalUrl) > 255) {
                    continue;
                }

                // ignore anchor links
                if (str_starts_with($originalUrl, '#')) {
                    continue;
                }

                $fullUrl = RelativeUrlResolver::resolve($originalUrl, $variantUrl);

                if (!$fullUrl) {
                    continue;
                }

                $urls[] = new ResolvedUrl($originalUrl, $fullUrl);
            }

            if (count($urls) === 0) {
                continue;
            }


            $variantIndexedUrls[$variant->id] = $urls;
        }

        return $variantIndexedUrls;
    }

    /**
     * @param PostVariant $variant
     * @param AnalyzedLinkDto[] $results
     */
    private function finalizeVariant(PostVariant $variant, array $results): void
    {
        /** @var string[] $ignoredLinksUrls */
        $ignoredLinksUrls = PostVariantLinkService::getIgnoredLinks($variant)
            ->pluck('url')
            ->toArray();

        // update link_analyzer_links table
        $links = PostVariantLinkService::updateLinksFromResults(
            $this->blog,
            $variant,
            $results,
            true,
            $ignoredLinksUrls
        );

        event(
            new OnLinkUpdateEvent(
                $variant,
                $links,
                $results,
            )
        );

        // update post_variants table to cache the results
        PostVariantLinkService::updatePostVariantCache(
            $variant,
            LinkAnalyzeService::getIgnoreAwareStatusFromLinks($links),
        );
    }

    private function getHrefFromLinkMark(Mark $linkMark): ?string
    {
        $href = $linkMark->attr('href', false);
        return is_string($href) ? $href : null;
    }

    private function findLanguageById(int $languageId): ?Language
    {
        return $this->languages->first(fn(Language $language) => $language->id === $languageId);
    }

}