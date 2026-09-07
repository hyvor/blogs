<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorTalk;

use App\Api\Console\Controller\HyvorTalkController;
use App\Entity\HyvorTalkWebsite;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\InterHyvorTalkWebsiteFactory;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Sdk\Auth\StaticTokenProvider;
use Hyvor\Sdk\Talk\TalkClient;
use PHPUnit\Framework\Attributes\CoversClass;
use Sentry\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(HyvorTalkController::class)]
#[CoversClass(HyvorTalkService::class)]
class DisconnectHyvorTalkIntegrationTest extends ApiTestCase
{
    public function test_disconnects_and_removes_the_row(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-disconnect-ok']);
        $hyvorTalk = InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 456, 'created_by_blogs' => true]);

        $htDeleteResponse = new MockResponse('', ['http_code' => 204]);
        $mockClient = new MockHttpClient([$htDeleteResponse]);
        $this->getContainer()->set(HttpClientInterface::class, $mockClient);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new TalkClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->getContainer()->set(CloudApiService::class, $cloudApiServiceMock);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-talk/disconnect', user: $owner);

        $this->assertResponseIsSuccessful();
        $found = $this->getEm()->getRepository(HyvorTalkWebsite::class)->findOneBy(['blog' => $blog]);
        $this->assertNull($found);

        $this->assertSame('DELETE', $htDeleteResponse->getRequestMethod());
        $this->assertStringContainsString((string) $hyvorTalk->getWebsiteId(), $htDeleteResponse->getRequestUrl());
    }

    public function test_disconnects_without_deleting_the_website_when_not_created_by_blogs(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-disconnect-not-owned']);
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'created_by_blogs' => false]);

        $mockClient = new MockHttpClient([]);
        $this->getContainer()->set(HttpClientInterface::class, $mockClient);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new TalkClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->getContainer()->set(CloudApiService::class, $cloudApiServiceMock);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-talk/disconnect', user: $owner);

        $this->assertResponseIsSuccessful();
        $found = $this->getEm()->getRepository(HyvorTalkWebsite::class)->findOneBy(['blog' => $blog]);
        $this->assertNull($found);
        $this->assertSame(0, $mockClient->getRequestsCount());
    }

    public function test_404_when_not_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-disconnect-missing']);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-talk/disconnect', user: $owner);

        $this->assertResponseStatusCodeSame(404);
    }
}
