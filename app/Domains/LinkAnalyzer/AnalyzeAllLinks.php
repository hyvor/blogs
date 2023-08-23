<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\Content\Marks\Link;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Hyvor\Phrosemirror\Document\Mark;

class AnalyzeAllLinks
{

    public int $postsCount = 0;
    public int $postVariantsCount = 0;

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
            ->chunk(1000, function ($posts) {
                foreach ($posts as $post) {
                    $this->analyzePost($post);
                }
            });

    }


    private function analyzePost(Post $post) : void
    {
        $this->postsCount++;
        foreach ($post->variants as $variant) {
            $this->analyzeVariant($variant);
        }
    }

    private function analyzeVariant(PostVariant $variant) : void
    {
        if ($variant->status !== PostStatusEnum::PUBLISHED) {
            return;
        }

        $this->postVariantsCount++;

        $content = $variant->content;
        if (!$content) {
            return;
        }

        $doc = PostContentService::getDocumentFromJson($content, $this->blog);
        $linkMarks = $doc->getMarks(Link::class);

        $urls = [];

        foreach ($linkMarks as $linkMark) {
            $url = self::getWebUrlFromLinkMark($linkMark, $this->baseUrl);
            if ($url) {
                $urls[] = $url;
            }
        }

        // who has more than 100 links in a post?
        $urls = array_slice($urls, 0, 100);

        $this->linksCount += count($urls);
        $results = LinkAnalyzerService::analyze($urls);
        LinkAnalyzerService::saveToDb(
            $this->blog,
            $variant,
            $results,
            true
        );

        foreach ($results as $url => $status) {
            $statusType = LinkStatusTypeEnum::fromStatus($status);

            if ($statusType === LinkStatusTypeEnum::OK) {
                $this->linksOkCount++;
            } else if ($statusType === LinkStatusTypeEnum::BROKEN) {
                $this->linksBrokenCount++;
            } else if ($statusType === LinkStatusTypeEnum::REDIRECT) {
                $this->linksRedirectCount++;
            } else if ($statusType === LinkStatusTypeEnum::IGNORED) {
                $this->linksIgnoredCount++;
            }
        }

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

        $scheme = parse_url($href, PHP_URL_SCHEME);

        if ($scheme === null) {
            // no scheme, so it's a relative URL
            $path = $href === '' ? '' : '/' . ltrim($href, '/');
            return $baseUrl . $path;
        } else if ($scheme === 'http' || $scheme === 'https') {
            // absolute URL
            return $href;
        }

        return null;
    }

}