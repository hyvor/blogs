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
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class SitemapPagesProcessor extends RouteProcessorAbstract
{
    private Blog $blog;

    private Collection $languages;

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {
        $this->blog = $pathMatcher->blog;
        $this->languages = LanguageRepository::getAllLanguages($this->blog);

        $pagesXML = $this->pagesXML();
        $indexXML = $this->indexXML();

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <urlset 
            xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
            xmlns:xhtml="http://www.w3.org/1999/xhtml"
        >
            $indexXML
            $pagesXML
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

    private function pagesXML(): string
    {
        return Post::join('post_variants', fn ($join) => $join
                    ->on('post_variants.post_id', '=', 'posts.id')
                    ->where('post_variants.language_id', '=', $this->languages->firstWhere('is_primary', true)->id)
        )
            ->where('post_variants.status', 'published')
            ->where('posts.blog_id', $this->blog->id)
            ->where('posts.is_page', true)
            ->select('posts.*')
            ->orderBy('posts.id', 'ASC')
            ->get()
            ->mapInto(UrlPostEntry::class)
            ->map(fn ($entry) => $entry->toXML())
            ->implode("\n");
    }

    private function indexXML(): string
    {
        $entry = new UrlEntry();

        foreach ($this->languages as $language) {
            $url = PermalinkRepository::getBlogPermalink($this->blog, $language);

            if ($language->is_primary) {
                $entry->loc($url);
            }

            $entry->langAlt($language->code, $url);
        }

        return $entry->toXML();
    }
}
