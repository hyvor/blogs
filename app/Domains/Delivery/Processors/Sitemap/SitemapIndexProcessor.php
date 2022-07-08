<?php

namespace App\Domains\Delivery\Processors\Sitemap;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\Processors\RouteProcessorAbstract;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Language\LanguageRepository;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;

class SitemapIndexProcessor extends RouteProcessorAbstract
{

    private Blog $blog;
    private Language $primaryLanguage;

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {
        $this->blog = $pathMatcher->blog;
        $this->primaryLanguage = LanguageRepository::getPrimaryLanguage($this->blog);

        $postSitemaps = $this->getPostSitemaps();

        /**
         * @var $sitemaps IndexEntry[]
         */
        $sitemaps = [
            new IndexEntry('sitemap-pages.xml'),
            ...$postSitemaps,
        ];

        $sitemapsXml = '';
        $baseUrl = PermalinkRepository::getFullUrlFromPath($this->blog);

        foreach ($sitemaps as $index => $sitemap) {
            $url = "$baseUrl/$sitemap->name";
            $sitemapsXml .=
                "<sitemap><loc>$url</loc></sitemap>" .
                ($index !== (count($sitemaps) - 1) ? "\n\t" : '');
        }

        $sitemapIndexContent = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            $sitemapsXml
        </sitemapindex>
        XML;

        $this->setResponseObject(
            DeliveryAPIResponseObject::forFile(
                DeliveryAPIFileTypeEnum::TEMPLATE,
                $sitemapIndexContent,
                'text/xml',
            )
        );
    }

    /**
     * @return IndexEntry[]
     */
    private function getPostSitemaps()
    {

        $postsCount = (int) Post::join('post_variants', fn ($join) =>
                $join
                    ->on('post_variants.post_id', '=', 'posts.id')
                    ->where('post_variants.language_id', '=', $this->primaryLanguage->id)
                )
            ->where('post_variants.status', 'published')
            ->where('posts.blog_id', $this->blog->id)
            ->where('posts.is_page', false)
            ->selectRaw('COUNT(posts.id) as posts_count')
            ->value('posts_count');

        if ($postsCount === 0)
            return [];

        $sitemapsCount = ceil($postsCount / config('limits.max_entries_per_sitemap'));

        $return = [];

        foreach (range(1, $sitemapsCount) as $number) {
            $return[] = new IndexEntry("sitemap-posts-$number.xml");
        }

        return $return;

    }

}