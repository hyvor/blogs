<?php declare(strict_types=1);

namespace App\Domains\Delivery\Processors\Sitemap;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\Processors\RouteProcessorAbstract;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class SitemapPostsProcessor extends RouteProcessorAbstract
{
    private Blog $blog;

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {
        $this->blog = $pathMatcher->blog;

        $number = (int) $matchedRoute->param('number');

        if ($number < 1) {
            return;
        }

        $posts = $this->getPosts($number);

        if ($posts->count() === 0) {
            return;
        }

        $postsXML = $posts->mapInto(UrlPostEntry::class)
            ->map(fn ($entry) => $entry->toXML())
            ->implode("\n");

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <urlset 
            xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
            xmlns:xhtml="http://www.w3.org/1999/xhtml"
        >
            $postsXML
        </urlset>
        XML;

        $this->setResponseObject(
            DeliveryAPIResponseObject::forFile(
                DeliveryAPIFileTypeEnum::TEMPLATE,
                $xml,
                'text/xml',
            )
        );
    }

    /**
     * @return Collection<int, Post>
     */
    private function getPosts(int $number) : Collection
    {
        $primaryLanguage = LanguageRepository::getPrimaryLanguage($this->blog);
        $limit = intval(config('limits.max_entries_per_sitemap'));

        return Post::join(
            'post_variants',
            fn ($join) => $join
                    ->on('post_variants.post_id', '=', 'posts.id')
                    ->where('post_variants.language_id', '=', $primaryLanguage->id)
        )
            ->where('post_variants.status', 'published')
            ->where('posts.blog_id', $this->blog->id)
            ->where('posts.is_page', false)
            ->select('posts.*')
            ->orderBy('posts.id', 'asc')
            ->limit($limit)
            ->offset(($number - 1) * $limit)
            ->get();
    }
}
