<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default\Fonts;

use App\Service\Delivery\CacheControl;
use App\Service\Delivery\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\Fonts\FontsFileProcessor;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(PathMatcher::class)]
#[CoversClass(FontsFileProcessor::class)]
class FontFileTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_returns_font_file(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('empty response', [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/font-woff2'],
            ]),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne();
        $response = $this->pathMatcher()->match($blog, '/fonts/file/mulish/files/mulish-latin-400-normal.woff2');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame('empty response', $response->content);
        $this->assertSame('application/font-woff2', $response->mimeType);
        $this->assertSame(CacheControl::ONE_YEAR, $response->cacheControl);
    }

    public function test_on_fail(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 500]),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $blog = BlogFactory::createOne();
        $response = $this->pathMatcher()->match($blog, '/fonts/file/mulish/files/mulish-latin-400-normal.woff2');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(500, $response->status);
        $this->assertStringContainsString('Failed to fetch font file', (string)$response->content);
        $this->assertSame('text/plain', $response->mimeType);
        $this->assertSame(CacheControl::NO_CACHE, $response->cacheControl);
    }
}
