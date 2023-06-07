<?php declare(strict_types=1);

namespace App\Domains\Import\Sitemap;

use App\Domains\Import\Importer\ImportingPost;
use App\Domains\Import\Importer\ImportingPostVariant;
use App\Domains\Import\Importer\ParserAbstract;
use App\Domains\Import\Importer\ParserException;
use App\Domains\Import\Sitemap\PageScraper\PageScraper;
use App\Domains\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Models\Blog;
use Illuminate\Support\Facades\Http;

class SitemapParser extends ParserAbstract
{

    public function __construct(
        private Blog $blog,
        private readonly string $sitemapUrl,
        private PageScraperOptions $pageScraperOptions
    ) {}

    public function parse() : void
    {

        $sitemap = Http::get($this->sitemapUrl);

        if (!$sitemap->ok()) {
            throw new ParserException('Cannot fetch sitemap');
        }

        $sitemap = $sitemap->body();

        $urls = explode("\n", $sitemap);

        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url)) {
                continue;
            }

            $scrapper = new PageScraper(
                $this->blog,
                $url,
                $this->pageScraperOptions
            );
            $scrapper->scrape();

            $this->addPost(new ImportingPost(
                publishedAt: $scrapper->publishedAt,
                featuredImageUrl: $scrapper->featuredImageUrl,
                variants: [
                    new ImportingPostVariant(
                        slug: $scrapper->slug,
                        content: $scrapper->content,
                        title: $scrapper->title,
                        description: $scrapper->description,
                    )
                ]
            ));
        }

    }

}