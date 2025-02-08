<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\AnalyzedLinkDto;
use App\Domains\LinkAnalyzer\FullUrl;
use App\Domains\LinkAnalyzer\LinkAnalyzeService;
use App\Domains\LinkAnalyzer\LinkStatusCheck\LinkStatusCheckService;
use App\Domains\LinkAnalyzer\PostVariantLinkService;
use App\Domains\LinkAnalyzer\LinkStatusTypeEnum;
use App\Domains\Post\Content\Marks\Link;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Hyvor\Phrosemirror\Document\Mark;
use Illuminate\Database\Eloquent\Collection;

class FullBlogAnalyzer
{

    public int $postsCount = 0;
    public int $pagesCount = 0;
    public int $postVariantsCount = 0;
    public int $pageVariantsCount = 0;

    public int $linksCount = 0;
    public int $linksOkCount = 0;
    public int $linksBrokenCount = 0;
    public int $linksRedirectCount = 0;
    public int $linksIgnoredCount = 0;

    public function __construct(
        private Blog $blog,
    ) {
    }

    public function analyze(): void
    {
        Post::where('blog_id', $this->blog->id)
            ->orderBy('id')
            ->chunk(100, function ($posts) {
                $this->analyzeChunk($posts);
            });
    }

    /**
     * @param Collection<int, Post> $posts
     */
    private function analyzeChunk(Collection $posts): void
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
        $statuses = $statusCheck->check($allFinalUrls);

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
     * @param Collection<int, Post> $posts
     * @return \Illuminate\Support\Collection<int, array<int, ResolvedUrl>>
     */
    private function getUrlsFromPosts(Collection $posts)
    {
        $variantIndexedUrls = collect();

        foreach ($posts as $post) {
            if ($post->is_page) {
                $this->pagesCount++;
            } else {
                $this->postsCount++;
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

                if ($post->is_page) {
                    $this->pageVariantsCount++;
                } else {
                    $this->postVariantsCount++;
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

                    $fullUrl = FullUrl::getFullUrl($originalUrl, $variantUrl);

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


        $links = PostVariantLinkService::updateLinksFromResults(
            $this->blog,
            $variant,
            $results,
            true,
            $ignoredLinksUrls
        );

        $this->linksCount += $links->count();

        // update the counts
        foreach ($links as $link) {
            $statusType = LinkStatusTypeEnum::fromStatus($link->status_code);

            if ($link->ignore) {
                $this->linksIgnoredCount++;
            } elseif ($statusType === LinkStatusTypeEnum::OK) {
                $this->linksOkCount++;
            } elseif ($statusType === LinkStatusTypeEnum::BROKEN) {
                $this->linksBrokenCount++;
            } elseif ($statusType === LinkStatusTypeEnum::REDIRECT) {
                $this->linksRedirectCount++;
            }
        }

        PostVariantLinkService::updatePostVariantCache(
            $variant,
            LinkAnalyzeService::getFrontendResultsFromLinks($links)
        );
    }

    private function getHrefFromLinkMark(Mark $linkMark): ?string
    {
        $href = $linkMark->attr('href', false);
        return is_string($href) ? $href : null;
    }

    /**
     * @deprecated
     * This should mirror link.ts in the frontend
     */
    public static function getWebUrlFromLinkMark(Mark $linkMark, string $baseUrl): ?string
    {
        $baseUrl = rtrim($baseUrl, '/');

        $href = $linkMark->attr('href', false);
        if (!is_string($href)) {
            return null;
        }

        return FullUrl::getFullUrl($href, $baseUrl);
    }

}