<?php

namespace App\Tests\Service\LinkAnalysis;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Service\LinkAnalysis\LinkStatusCheckService;
use App\Service\Route\PermalinkService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(LinkStatusCheckService::class)]
class LinkStatusCheckServiceTest extends KernelTestCase
{
    /**
     * @param string[] $urls
     * @return array{0: string[], 1: string[]}
     */
    private function callSeparateUrls(array $urls, ?Blog $blog): array
    {
        $service = $this->getService(LinkStatusCheckService::class);
        $method = new ReflectionMethod(LinkStatusCheckService::class, 'separateUrls');
        /** @var array{0: string[], 1: string[]} $result */
        $result = $method->invoke($service, $urls, $blog);
        return $result;
    }

    public function test_separates_internal_and_external_urls(): void
    {
        $blog = BlogFactory::createOne([
            'subdomain' => 'link-check-sep',
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
        ]);

        $blogUrl = $this->getService(PermalinkService::class)->getBlogUrl($blog);

        [$internal, $external] = $this->callSeparateUrls([
            $blogUrl . '/post-1',
            $blogUrl . '/post-2',
            'https://external.example.com',
            'https://another.example.com/page',
        ], $blog);

        $this->assertCount(2, $internal);
        $this->assertContains($blogUrl . '/post-1', $internal);
        $this->assertContains($blogUrl . '/post-2', $internal);
        $this->assertCount(2, $external);
        $this->assertContains('https://external.example.com', $external);
        $this->assertContains('https://another.example.com/page', $external);
    }

    public function test_all_urls_are_external_when_no_blog(): void
    {
        [$internal, $external] = $this->callSeparateUrls([
            'https://example.com',
            'https://another.com',
        ], null);

        $this->assertCount(0, $internal);
        $this->assertCount(2, $external);
    }

    public function test_checks_external_urls(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('OK', ['http_code' => 200]),
            new MockResponse('Not Found', ['http_code' => 404]),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $service = $this->getService(LinkStatusCheckService::class);
        $results = $service->check([
            'https://example.com/ok',
            'https://example.com/missing',
        ]);

        $this->assertSame(200, $results['https://example.com/ok']->httpStatus);
        $this->assertSame(404, $results['https://example.com/missing']->httpStatus);
    }

    public function test_deduplicates_urls(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('OK', ['http_code' => 200]),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $service = $this->getService(LinkStatusCheckService::class);
        $results = $service->check([
            'https://example.com/page',
            'https://example.com/page',
        ]);

        $this->assertCount(1, $results);
        $this->assertSame(1, $mockClient->getRequestsCount());
    }
}
