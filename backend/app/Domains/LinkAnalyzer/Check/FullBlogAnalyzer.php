<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\FullUrl;
use App\Domains\LinkAnalyzer\PostVariantLinkService;
use App\Domains\LinkAnalyzer\LinkAnalyzeService;
use App\Domains\LinkAnalyzer\LinkStatusTypeEnum;
use App\Domains\Post\Content\Marks\Link;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Hyvor\Phrosemirror\Document\Mark;

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

    private string $baseUrl;

    public function __construct(
        private Blog $blog
    ) {
       $this->baseUrl = PermalinkRepository::getBaseUrl($blog);
    }

    public function analyze() : void
    {

        Post::where('blog_id', $this->blog->id)
            ->orderBy('id')
            ->chunk(1000, function ($posts) {
                foreach ($posts as $post) {
                    $this->analyzePost($post);
                }
            });

    }


    private function analyzePost(Post $post) : void
    {
        if ($post->is_page) {
            $this->pagesCount++;
        } else {
            $this->postsCount++;
        }
        foreach ($post->variants as $variant) {
            $this->analyzeVariant($post, $variant);
        }
    }

    private function analyzeVariant(Post $post, PostVariant $variant) : void
    {
        if ($variant->status !== PostStatusEnum::PUBLISHED) {
            return;
        }

        $content = $variant->content;
        if (!$content) {
            return;
        }

        if ($post->is_page) {
            $this->pageVariantsCount++;
        } else {
            $this->postVariantsCount++;
        }

        $doc = PostContentService::getDocumentFromJson($content, $this->blog);
        $linkMarks = $doc->getMarks(Link::class);

        $urls = [];

        foreach ($linkMarks as $linkMark) {
            $url = self::getWebUrlFromLinkMark($linkMark, $this->baseUrl);

            if (!$url)
                continue;

            if (mb_strlen($url) > 255) {
                continue;
            }

            $urls[] = $url;
        }

        // who has more than 100 links in a post?
        $urls = array_slice($urls, 0, 100);

        $results = LinkAnalyzeService::analyzePostVariantLinks(
            $this->blog,
            $variant,
            $urls,
        );

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

        foreach ($links as $link) {
            $statusType = LinkStatusTypeEnum::fromStatus($link->status_code);

            if ($link->ignore) {
                $this->linksIgnoredCount++;
            } else if ($statusType === LinkStatusTypeEnum::OK) {
                $this->linksOkCount++;
            } else if ($statusType === LinkStatusTypeEnum::BROKEN) {
                $this->linksBrokenCount++;
            } else if ($statusType === LinkStatusTypeEnum::REDIRECT) {
                $this->linksRedirectCount++;
            }

        }

        PostVariantLinkService::updatePostVariantCache(
            $variant,
            LinkAnalyzeService::getResultsFromLinks($links)
        );

    }

    /**
     * This should mirror link.ts in the frontend
     */
    public static function getWebUrlFromLinkMark(Mark $linkMark, string $baseUrl) : ?string
    {
        $baseUrl = rtrim($baseUrl, '/');

        $href = $linkMark->attr('href', false);
        if (!is_string($href)) {
            return null;
        }

        return FullUrl::getFullUrl($href, $baseUrl);
    }

}