<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default\Fonts;

use App\Service\Delivery\CacheControl;
use App\Service\Delivery\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\Fonts\FontsCssProcessor;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(PathMatcher::class)]
#[CoversClass(FontsCssProcessor::class)]
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
        $fontCssResponse = "@font-face { font-family: 'Mulish'; src: url(https://fonts.bunny.net/mulish/files/mulish-latin-400-normal.woff2); }";

        $mockClient = new MockHttpClient([
            new MockResponse($fontCssResponse),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne(['hosting_at' => \App\Entity\Enum\BlogHostingAt::SUBDOMAIN]);
        $response = $this->pathMatcher()->match($blog, '/fonts/css/mulish:400');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame('text/css', $response->mimeType);
        $this->assertSame(CacheControl::ONE_YEAR, $response->cacheControl);
        // font URLs are rewritten to local paths
        $this->assertStringNotContainsString('fonts.bunny.net/mulish', (string)$response->content);
        $this->assertStringContainsString('/fonts/file/mulish', (string)$response->content);
    }

    public function test_on_fail(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 500]),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne();
        $response = $this->pathMatcher()->match($blog, '/fonts/css/mulish:400');

        $this->assertSame(500, $response->status);
        $this->assertStringContainsString('Failed to fetch font css', (string)$response->content);
        $this->assertSame(CacheControl::NO_CACHE, $response->cacheControl);
    }
}
