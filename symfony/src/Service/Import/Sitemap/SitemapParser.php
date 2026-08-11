<?php

namespace App\Service\Import\Sitemap;

use App\Entity\Blog;
use App\Service\App\HttpBot;
use App\Service\AppConfig;
use App\Service\Import\Importer\ImportingPost;
use App\Service\Import\Importer\ImportingPostVariant;
use App\Service\Import\Importer\ParserAbstract;
use App\Service\Import\Importer\ParserException;
use App\Service\Import\Sitemap\PageScraper\PageScraper;
use App\Service\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Service\Post\Content\PostContentService;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SitemapParser extends ParserAbstract
{
    public function __construct(
        private readonly Blog $blog,
        private readonly string $sitemapUrl,
        private readonly PageScraperOptions $pageScraperOptions,
        private readonly HttpClientInterface $httpClient,
        private readonly PostContentService $postContentService,
        private AppConfig $appConfig
    ) {
    }

    /** @throws ParserException */
    public function parse(): void
    {
        $sitemap = null;

        try {
            $sitemap = $this->httpClient->request('GET', $this->sitemapUrl, [
                'headers' => ['User-Agent' => $this->appConfig->getHttpBotUserAgent()],
                'timeout' => 10,
            ]);
            $ok = $sitemap->getStatusCode() >= 200 && $sitemap->getStatusCode() < 300;
        } catch (HttpClientExceptionInterface) {
            $ok = false;
        }

        if (!$ok || $sitemap === null) {
            throw new ParserException('Cannot fetch sitemap');
        }

        $urls = $this->getUrls($sitemap);

        foreach ($urls as $url) {
            $scraper = new PageScraper(
                $this->blog,
                $url,
                $this->pageScraperOptions,
                $this->httpClient,
                $this->postContentService,
                $this->appConfig->getHttpBotUserAgent()
            );
            $scraper->scrape();

            $error = $scraper->getError();

            if ($error) {
                throw new ParserException("Error {$error->value} while scraping $url");
            }

            $this->addPost(new ImportingPost(
                publishedAt: $scraper->publishedAt,
                featuredImageUrl: $scraper->featuredImageUrl,
                variants: [
                    new ImportingPostVariant(
                        slug: $scraper->slug,
                        content: $scraper->content,
                        title: $scraper->title,
                        description: $scraper->description,
                    ),
                ],
            ));
        }
    }

    /**
     * @return array<string>
     */
    private function getUrls(\Symfony\Contracts\HttpClient\ResponseInterface $response): array
    {
        $sitemap = $response->getContent(false);
        $sitemap = ltrim($sitemap);

        $contentType = $response->getHeaders(false)['content-type'][0] ?? '';
        $isXml = str_contains($contentType, 'xml') || str_starts_with($sitemap, '<?xml');

        if ($isXml) {
            $urls = [];

            $crawler = new Crawler($sitemap);

            try {
                $filtered = $crawler->filter('default|url default|loc');
            } catch (\Exception) {
                $filtered = $crawler->filter('url loc');
            }

            $filtered->each(function ($node) use (&$urls) {
                $urls[] = $node->text();
            });
        } else {
            $urls = array_filter(
                array_map('trim', explode("\n", $sitemap)),
                fn($url) => $url !== '',
            );
        }

        return array_values(array_filter(
            $urls,
            fn($url) => filter_var($url, FILTER_VALIDATE_URL) !== false,
        ));
    }
}
