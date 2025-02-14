<?php

namespace App\Domains\LinkAnalyzer\Check;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\AnalyzedLinkDto;
use App\Domains\LinkAnalyzer\RelativeUrlResolver;
use App\Domains\LinkAnalyzer\LinkAnalyzeService;
use App\Domains\LinkAnalyzer\LinkStatusCheck\LinkStatusCheckService;
use App\Domains\LinkAnalyzer\PostVariantLinkService;
use App\Domains\Post\Content\Marks\Link;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Hyvor\Phrosemirror\Document\Mark;


class PostsCheck
{

    public function __construct(

        private Blog $blog,

        /**
         * Called when a post is about to be analyzed
         * @var null|callable(Post): void
         */
        private $onPostStart = null,

        /**
         * Called when a post variant is chosen to be analyzed soon
         * @var null|callable(PostVariant, Post): void
         */
        private $onPostVariantStart = null,

        /**
         * Called when links are updated for a certain variant
         * @var null|callable(PostVariant, Collection<int, LinkAnalyzerLink> AnalyzedLinkDto[]): void
         */
        private $onLinksUpdate = null,

    ) {
    }

    /**
     * @param iterable<Post> $posts
     */
    public function check(iterable $posts): void
    {
        // [variantId => [ResolvedUrl, ResolvedUrl, ...]]
        $variantIndexedUrls = $this->getUrlsFromPosts($posts);
        $allFinalUrls = $variantIndexedUrls->map(fn($urls) => array_map(
            fn($url) => $url->fullUrl,
            $urls
        ))->toArray();
        $allFinalUrls = array_merge(...$allFinalUrls);

        // check HTTP statuses
        $statusCheck = app(LinkStatusCheckService::class);
        $statuses = $statusCheck->check($allFinalUrls, $this->blog);

        foreach ($posts as $post) {
            foreach ($post->variants as $variant) {
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
    }

    /**
     * @param iterable<Post> $posts
     * @return \Illuminate\Support\Collection<int, array<int, ResolvedUrl>>
     */
    private function getUrlsFromPosts(iterable $posts)
    {
        $variantIndexedUrls = collect();

        foreach ($posts as $post) {
            if ($this->onPostStart) {
                ($this->onPostStart)($post);
            }

            foreach ($post->variants as $variant) {
                $urls = [];

                if ($variant->status !== PostStatusEnum::PUBLISHED) {
                    continue;
                }

                $content = $variant->content;
                if (!$content) {
                    continue;
                }

                if ($this->onPostVariantStart) {
                    ($this->onPostVariantStart)($variant, $post);
                }

                $doc = PostContentService::getDocumentFromJson($content, $this->blog);
                $linkMarks = $doc->getMarks(Link::class);
                $variantUrl = PermalinkRepository::getPostPermalink($post, $this->blog, $variant->language);

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

        if ($this->onLinksUpdate) {
            ($this->onLinksUpdate)($variant, $links, $results);
        }

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

}