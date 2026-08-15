<?php

namespace App\Tests\Service\Integration\HyvorTalk;

use App\Entity\Enum\BlogHostingAt;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use App\Service\Integration\HyvorTalk\SyncDomainsMessage;
use App\Service\Integration\HyvorTalk\SyncDomainsMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\InterHyvorTalkWebsiteFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Sdk\Auth\StaticTokenProvider;
use Hyvor\Sdk\Talk\TalkClient;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(SyncDomainsMessageHandler::class)]
#[CoversClass(SyncDomainsMessage::class)]
#[CoversClass(HyvorTalkService::class)]
class SyncDomainsMessageHandlerTest extends KernelTestCase
{

    public function test_syncs_domains_of_blog_to_hyvor_talk(): void
    {
        $blog = BlogFactory::createOne([
            'organization_id' => 555,
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
            'subdomain' => 'my-blog',
        ]);
        $hyvorTalk = InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 777]);

        $requests = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url, array $options) use (&$requests): JsonMockResponse {
                $requests[] = [
                    'method' => $method,
                    'url' => $url,
                    'body' => json_decode($options['body'], true),
                ];

                return new JsonMockResponse([]);
            }
        );

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new TalkClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncDomainsMessage($blog->getId()));
        $transport->processOrFail(1);

        $this->assertCount(1, $requests);

        $request = $requests[0];
        $this->assertSame('POST', $request['method']);
        $this->assertStringContainsString('/777/domains', $request['url']);
        $this->assertSame(['my-blog.hyvorblogs.io'], $request['body']['domains']);
        $this->assertSame('add', $request['body']['operation']);
    }

    public function test_does_nothing_when_blog_does_not_exist(): void
    {
        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willThrowException(new \RuntimeException('should not be called'));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncDomainsMessage(999999999));
        $transport->processOrFail(1);

        $this->addToAssertionCount(1);
    }

    public function test_does_nothing_when_blog_has_no_organization(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => null]);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willThrowException(new \RuntimeException('should not be called'));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncDomainsMessage($blog->getId()));
        $transport->processOrFail(1);

        $this->addToAssertionCount(1);
    }

    public function test_does_nothing_when_blog_not_connected_to_hyvor_talk(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 555]);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willThrowException(new \RuntimeException('should not be called'));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncDomainsMessage($blog->getId()));
        $transport->processOrFail(1);

        $this->addToAssertionCount(1);
    }

}
