<?php

namespace App\Tests\Service\Import\Sitemap;

use App\Service\Import\Importer\ParserException;
use App\Service\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Service\Import\Sitemap\SitemapParser;
use App\Service\Post\Content\PostContentService;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(SitemapParser::class)]
class SitemapParserTest extends KernelTestCase
{
    /**
     * @param array<string, MockResponse> $responses
     */
    private function parser(array $responses, string $sitemapUrl = 'https://example.com/sitemap.txt'): SitemapParser
    {
        $mockClient = new MockHttpClient(function (string $method, string $url) use ($responses) {
            foreach ($responses as $matchUrl => $response) {
                if ($url === $matchUrl) {
                    return $response;
                }
            }
            return new MockResponse('', ['http_code' => 404]);
        });
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne();

        return new SitemapParser(
            $blog,
            $sitemapUrl,
            new PageScraperOptions(contentSelector: 'article'),
            $this->getService(HttpClientInterface::class),
            $this->getService(PostContentService::class),
        );
    }

    /** @throws ParserException */
    public function test_parses_sitemaps(): void
    {
        $parser = $this->parser([
            'https://example.com/sitemap.txt' => new MockResponse(<<<TXT
                https://example.com/page1
                https://example.com/page2
                wrong URL
                TXT),
            'https://example.com/page1' => new MockResponse(<<<HTML
                <title>Page 1</title>
                <meta name="description" content="Page 1 description">
                <article><p>Page 1</p></article>
                HTML),
            'https://example.com/page2' => new MockResponse(<<<HTML
                <title>Page 2</title>
                <meta name="description" content="Page 2 description">
                <article><p>Page 2</p></article>
                HTML),
        ]);
        $parser->parse();

        $this->assertCount(2, $parser->posts);
        $this->assertSame('Page 1', $parser->posts[0]->variants[0]->title);
        $this->assertSame('Page 2', $parser->posts[1]->variants[0]->title);
    }

    /** @throws ParserException */
    public function test_parses_xml_sitemaps(): void
    {
        $xml = <<<XML
            <urlset
                xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
                xmlns:xhtml="http://www.w3.org/1999/xhtml"
            >
                <url>
                    <loc>https://example.com/page1</loc>
                </url>
                <url>
                    <loc>https://example.com/page2</loc>
                </url>
                <url>
                    <loc>invalido url</loc>
                </url>
            </urlset>
            XML;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;

        $parser = $this->parser([
            'https://example.com/sitemap.txt' => new MockResponse($xml, ['response_headers' => ['Content-Type' => 'application/xml']]),
            'https://example.com/page1' => new MockResponse(<<<HTML
                <title>Page 1</title>
                <meta name="description" content="Page 1 description">
                <article><p>Page 1</p></article>
                HTML),
            'https://example.com/page2' => new MockResponse(<<<HTML
                <title>Page 2</title>
                <meta name="description" content="Page 2 description">
                <article><p>Page 2</p></article>
                HTML),
        ]);
        $parser->parse();

        $this->assertCount(2, $parser->posts);
        $this->assertSame('Page 1', $parser->posts[0]->variants[0]->title);
        $this->assertSame('Page 2', $parser->posts[1]->variants[0]->title);
    }

    /** @throws ParserException */
    public function test_error_handling_when_fetching_fails(): void
    {
        $parser = $this->parser([
            'https://example.com/sitemap.txt' => new MockResponse('https://example.com/page1'),
            'https://example.com/page1' => new MockResponse('', ['http_code' => 404]),
        ]);

        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Error cannot_fetch while scraping https://example.com/page1');

        $parser->parse();
    }
}
