<?php

namespace App\Domains\LinkAnalyzer\PostVariantsCheck;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\AnalyzedLinkDto;
use App\Domains\LinkAnalyzer\Check\ResolvedUrl;
use App\Domains\LinkAnalyzer\LinkAnalyzeService;
use App\Domains\LinkAnalyzer\LinkStatusCheck\IgnoreReasonEnum;
use App\Domains\LinkAnalyzer\LinkStatusCheck\LinkStatusCheckService;
use App\Domains\LinkAnalyzer\LinkStatusCheck\StatusCheckType;
use App\Domains\LinkAnalyzer\LinkStatusCheck\StatusResult;
use App\Domains\LinkAnalyzer\PostVariantLinkService;
use App\Domains\LinkAnalyzer\RelativeUrlResolver;
use App\Domains\Post\Content\Marks\Link;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\LinkAnalyzerLink;
use App\Models\PostVariant;
use Hyvor\Phrosemirror\Document\Mark;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;

use function PHPStan\dumpType;

class PostVariantsCheck
{

    /**
     * @var Collection<int, Language>
     */
    private Collection $languages;

    public function __construct(
        private readonly Blog $blog,
        private readonly bool $clearVariantCache = true,
    ) {
    }

    /**
     * Each variant model should have at least the following fields:
     *
     * - id
     * - content
     * - language_id
     *
     * @param iterable<PostVariant> $variants
     * @param \Illuminate\Support\Collection<int, ResolvedUrl[]>|null $urls indexed by variant ID
     */
    public function check(
        iterable $variants,
        $urls = null
    ): void {
        // [variantId => [ResolvedUrl, ResolvedUrl, ...]]
        $variantIndexedUrls = $urls ?? $this->getUrlsFromVariants($variants);
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
                    status: $statuses[$url->fullUrl] ??
                    new StatusResult(
                        type: StatusCheckType::EXTERNAL,
                        ignored: true,
                        ignoreReason: IgnoreReasonEnum::INTERNAL_ERROR,
                        comment: 'No status found'
                    ),
                ),
                $urls
            );

            $this->finalizeVariant($variant, $results);
        }
    }

    /**
     * @param PostVariant $variant
     * @param array<string>|null $urls
     * @return Collection<int, LinkAnalyzerLink>
     */
    public function checkOne(
        PostVariant $variant,
        ?array $urls = null
    ) {
        $variantUrl = PermalinkRepository::getPostVariantPermalink(
            $this->blog,
            $variant,
            $this->findLanguageById($variant->language_id)
        );
        $links = null;

        Event::listen(OnLinkUpdateEvent::class, function (OnLinkUpdateEvent $event) use (&$links) {
            $links = $event->links;
        });

        $resolvedUrls = null;

        if ($urls) {
            $resolvedUrls = array_map(fn(string $url) => $this->resolveUrl($url, $variantUrl), $urls);
            $resolvedUrls = array_filter($resolvedUrls);
            $resolvedUrls = collect([
                $variant->id => $resolvedUrls
            ]);
        }

        $this->check([$variant], $resolvedUrls);

        assert($links !== null);
        return $links;
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


            $variantIndexedUrls[$variant->id] = $urls;
        }

        return $variantIndexedUrls;
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

        $fullUrl = RelativeUrlResolver::resolve($originalUrl, $variantUrl);

        if (!$fullUrl) {
            return null;
        }

        return new ResolvedUrl($originalUrl, $fullUrl);
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
            $this->clearVariantCache,
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
        if (!isset($this->languages)) {
            $this->languages = $this->blog->languages;
        }

        return $this->languages->first(fn(Language $language) => $language->id === $languageId);
    }

}