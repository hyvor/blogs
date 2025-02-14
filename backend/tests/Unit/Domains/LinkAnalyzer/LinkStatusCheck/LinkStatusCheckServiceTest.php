<?php

namespace Tests\Unit\Domains\LinkAnalyzer\LinkStatusCheck;

use App\Domains\LinkAnalyzer\LinkStatusCheck\ExternalStatusCheck;
use App\Domains\LinkAnalyzer\LinkStatusCheck\InternalStatusCheck;
use App\Domains\LinkAnalyzer\LinkStatusCheck\LinkStatusCheckService;
use App\Models\Blog;
use Database\Factories\BlogFactory;
use Database\Factories\PostFactory;
use Database\Factories\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\Case\DatabaseTestCase;

#[CoversClass(LinkStatusCheckService::class)]
#[CoversClass(ExternalStatusCheck::class)]
#[CoversClass(InternalStatusCheck::class)]
class LinkStatusCheckServiceTest extends DatabaseTestCase
{

    public function testSeparatesInternalAndExternalLinks(): void
    {
        $blog = Blog::factory()->create([
            'subdomain' => 'example',
        ]);

        $service = $this->app->make(LinkStatusCheckService::class);

        $reflect = new \ReflectionClass($service);
        $method = $reflect->getMethod('separateInternalAndExternalUrls');

        [$internal, $external] = $method->invokeArgs($service, [
            [
                // external
                'https://example.com',
                'https://example.com/blog',
                'https://hyvor.com/about',
                'https://other.hyvorblogs.io',

                // internal
                'https://example.hyvorblogs.io',
                'https://example.hyvorblogs.io/blog',
                'https://example.hyvorblogs.io/assets/image.jpg',
            ],
            $blog,
        ]);

        $this->assertSame([
            'https://example.hyvorblogs.io',
            'https://example.hyvorblogs.io/blog',
            'https://example.hyvorblogs.io/assets/image.jpg',
        ], $internal);

        $this->assertSame([
            'https://example.com',
            'https://example.com/blog',
            'https://hyvor.com/about',
            'https://other.hyvorblogs.io',
        ], $external);

        [$internal, $external] = $method->invokeArgs($service, [
            [
                'https://example.com',
                'https://other.hyvorblogs.io',
                'https://example.hyvorblogs.io',
                'https://example.hyvorblogs.io/assets/image.jpg',
            ],
            null,
        ]);

        $this->assertCount(0, $internal);
        $this->assertCount(4, $external);
    }

    public function testGetsStatusOfExternalUrls(): void
    {
        // https://symfony.com/doc/current/http_client.html#testing-network-transport-exceptions
        $this->app->bind(HttpClientInterface::class, fn() => new MockHttpClient([

            // success
            new MockResponse('OK', ['http_code' => 200]),
            new MockResponse('Redirect', ['http_code' => 301]),
            new MockResponse('Server error', ['http_code' => 500]),

            // errors
            new MockResponse(info: ['error' => 'host unreachable']),
        ]));

        $urls = [
            'https://hyvor.com',
            'https://supun.io/about-old',
            'https://500.com',
            'https://invalid-host.com',
        ];

        $service = $this->app->make(LinkStatusCheckService::class);
        $this->assertInstanceOf(LinkStatusCheckService::class, $service);
        $result = $service->check($urls);

        $this->assertSame(200, $result['https://hyvor.com']->httpStatus);
        $this->assertSame(301, $result['https://supun.io/about-old']->httpStatus);
        $this->assertSame(500, $result['https://500.com']->httpStatus);
        $this->assertSame(500, $result['https://invalid-host.com']->httpStatus);
    }

    public function testGetsStatusOfInternalLinks(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();
        ThemeFileFactory::templateFor($blog, 'Hello!');
        ThemeFileFactory::templateFor($blog, 'Page', 'post.twig');

        PostFactory::oneFor($blog, variantAttr: [
            'slug' => 'about',
            'status' => 'published',
        ]);

        $blogUrl = "https://{$blog->subdomain}.hyvorblogs.io";

        $urls = [
            $blogUrl,
            "$blogUrl/about",
            "$blogUrl/otherpage",
            "$blogUrl/assets/image.jpg",
        ];

        $service = $this->app->make(LinkStatusCheckService::class);
        $this->assertInstanceOf(LinkStatusCheckService::class, $service);
        $result = $service->check($urls, $blog);

        $this->assertSame(200, $result[$blogUrl]->httpStatus);
        $this->assertSame(200, $result["$blogUrl/about"]->httpStatus);
        $this->assertSame(404, $result["$blogUrl/otherpage"]->httpStatus);
        $this->assertSame(404, $result["$blogUrl/assets/image.jpg"]->httpStatus);
    }

}