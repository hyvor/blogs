<?php declare(strict_types=1);

namespace App\Domains\Import\Sitemap;

use App\Domains\Import\Importer\ImportingPost;
use App\Domains\Import\Importer\ImportingPostVariant;
use App\Domains\Import\Importer\ParserAbstract;
use App\Domains\Import\Importer\ParserException;
use App\Domains\Import\ImportException;
use App\Domains\Import\Sitemap\PageScraper\PageScraper;
use App\Domains\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Models\Blog;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class SitemapParser extends ParserAbstract
{

    public function __construct(
        private Blog $blog,
        private readonly string $sitemapUrl,
        private readonly PageScraperOptions $pageScraperOptions
    ) {}

    public function parse() : void
    {

        $sitemap = Http::get($this->sitemapUrl);

        if (!$sitemap->ok()) {
            throw new ParserException('Cannot fetch sitemap');
        }

        $urls = $this->getUrls($sitemap);

        foreach ($urls as $url) {

            $scrapper = new PageScraper(
                $this->blog,
                $url,
                $this->pageScraperOptions
            );
            $scrapper->scrape();

            $error = $scrapper->getError();

            if ($error) {
                throw new ParserException(
                    "Error $error->value while scraping $url"
                );
            }

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

    /**
     * @return array<string>
     */
    private function getUrls(Response $response) : array
    {


        $sitemap = $response->body();
        $sitemap = ltrim($sitemap);

        $isXml = str_contains($response->header('Content-Type'), 'xml') ||
            str_starts_with($sitemap, '<?xml');

        if ($isXml) {

            $urls = collect([]);

            $crawler = new Crawler($sitemap);

            /**
             * Symfony Crawler unnecessarily removes the default namespace prefix
             * if there is only one namespace,
             * which causes `default|url default|loc` to fail
             * `url loc` does not work either when there are multiple namespaces
             */
            try {
                $filtered = $crawler->filter('default|url default|loc');
            } catch (Exception) {
                $filtered = $crawler->filter('url loc');
            }

            $filtered->each(function ($node) use (&$urls) {
                $urls->add($node->text());
            });

        } else {

            $urls = collect(explode("\n", $sitemap))
                ->map(fn($url) => trim($url))
                ->filter(fn($url) => !empty($url));

        }

        /** @var string[] $ret */
        $ret = $urls
            ->filter(fn($url) => filter_var($url, FILTER_VALIDATE_URL) !== false)
            ->toArray();

        return $ret;

    }

}