<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default\Fonts;

use App\Service\Delivery\Dto\CacheControl;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\Fonts\FontsCssProcessor;
use App\Service\Integration\Bunny\BunnyService;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(PathMatcher::class)]
#[CoversClass(FontsCssProcessor::class)]
#[CoversClass(BunnyService::class)]
class FontCssTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getService(CacheInterface::class)->clear();
    }

    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_returns_font_css(): void
    {
        $cssResponse = "@font-face {
  font-family: 'Mulish';
  font-style: normal;
  font-weight: 400;
  font-stretch: 100%;
  src: url(https://fonts.bunny.net/mulish/files/mulish-latin-400-normal.woff2) format('woff2'), url(https://fonts.bunny.net/mulish/files/mulish-latin-400-normal.woff) format('woff'); 
  unicode-range: U+0000-00FF;
}";

        $mockResponse = new MockResponse($cssResponse);
        $mockClient = new MockHttpClient($mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne(['hosting_at' => \App\Entity\Enum\BlogHostingAt::SUBDOMAIN]);
        $blogUrl = $this->getService(\App\Service\Route\PermalinkService::class)->getBlogUrl($blog);

        $replaced = "@font-face {
  font-family: 'Mulish';
  font-style: normal;
  font-weight: 400;
  font-stretch: 100%;
  src: url($blogUrl/fonts/file/mulish/files/mulish-latin-400-normal.woff2) format('woff2'), url($blogUrl/fonts/file/mulish/files/mulish-latin-400-normal.woff) format('woff'); 
  unicode-range: U+0000-00FF;
}";

        $response = $this->pathMatcher()->match($blog, '/fonts/css/mulish:400');

        $this->assertSame($replaced, $response->content);
        $this->assertSame(200, $response->status);
        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(DeliveryFileType::ASSET, $response->fileType);
        $this->assertSame('text/css', $response->mimeType);
        $this->assertSame(CacheControl::ONE_YEAR, $response->cacheControl);

        // Verify correct URL was requested
        $requests = $mockClient->getRequestsCount();
        $this->assertSame(1, $requests);
        $this->assertSame('https://fonts.bunny.net/css?family=mulish:400&display=swap', $mockResponse->getRequestUrl());

        // cache
        $cacheKey = "bunny_fonts_{$blog->getId()}_" . md5('https://fonts.bunny.net/css?family=mulish:400&display=swap');
        $cache = $this->getService(CacheInterface::class);
        $this->assertSame($replaced, $cache->get($cacheKey, function () {
            return 'default';
        }));
    }

    public function test_on_fail(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 500]),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne();
        $response = $this->pathMatcher()->match($blog, '/fonts/css/mulish:400');

        $this->assertSame('Failed to fetch font css: Request failed', $response->content);
        $this->assertSame(500, $response->status);
        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(DeliveryFileType::ASSET, $response->fileType);

        $cacheKey = "bunny_fonts_{$blog->getId()}_" . md5('https://fonts.bunny.net/css?family=mulish:400&display=swap');
        $cache = $this->getService(CacheInterface::class);
        $this->assertSame('default', $cache->get($cacheKey, function () {
            return 'default';
        }));
    }
}
