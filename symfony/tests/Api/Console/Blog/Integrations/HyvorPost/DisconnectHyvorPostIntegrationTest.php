<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorPost;

use App\Api\Console\Controller\HyvorPostController;
use App\Entity\HyvorPost;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Sdk\Auth\StaticTokenProvider;
use Hyvor\Sdk\Post\PostClient;
use PHPUnit\Framework\Attributes\CoversClass;
use Sentry\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(HyvorPostController::class)]
#[CoversClass(HyvorPostService::class)]
class DisconnectHyvorPostIntegrationTest extends ApiTestCase
{
    public function test_disconnects_and_removes_the_row(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-disconnect-ok']);
        $hyvorPost = HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 456, 'created_by_blogs' => true]);

        $hpDeleteResponse = new MockResponse('', ['http_code' => 204]);
        $mockClient = new MockHttpClient([$hpDeleteResponse]);
        $this->getContainer()->set(HttpClientInterface::class, $mockClient);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new PostClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->getContainer()->set(CloudApiService::class, $cloudApiServiceMock);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-post/disconnect', user: $owner);

        $this->assertResponseIsSuccessful();
        $found = $this->getEm()->getRepository(HyvorPost::class)->findOneBy(['blog' => $blog]);
        $this->assertNull($found);

        $this->assertSame('DELETE', $hpDeleteResponse->getRequestMethod());
        $this->assertStringContainsString('/newsletter', $hpDeleteResponse->getRequestUrl());
        $normalizedHeaders = $hpDeleteResponse->getRequestOptions()['normalized_headers'];
        $this->assertIsArray($normalizedHeaders);
        $newsletterIdHeaders = $normalizedHeaders['x-newsletter-id'] ?? null;
        $this->assertIsArray($newsletterIdHeaders);
        $this->assertSame('X-Newsletter-Id: '.$hyvorPost->getNewsletterId(), $newsletterIdHeaders[0]);
    }

    public function test_disconnects_without_deleting_the_newsletter_when_not_created_by_blogs(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-disconnect-not-owned']);
        HyvorPostFactory::createOne(['blog' => $blog, 'created_by_blogs' => false]);

        $mockClient = new MockHttpClient([]);
        $this->getContainer()->set(HttpClientInterface::class, $mockClient);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new PostClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->getContainer()->set(CloudApiService::class, $cloudApiServiceMock);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-post/disconnect', user: $owner);

        $this->assertResponseIsSuccessful();
        $found = $this->getEm()->getRepository(HyvorPost::class)->findOneBy(['blog' => $blog]);
        $this->assertNull($found);
        $this->assertSame(0, $mockClient->getRequestsCount());
    }

    public function test_404_when_not_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-disconnect-missing']);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-post/disconnect', user: $owner);

        $this->assertResponseStatusCodeSame(404);
    }
}
