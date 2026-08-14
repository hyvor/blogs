<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorTalk;

use App\Api\Console\Controller\HyvorTalkController;
use App\Entity\Enum\UserRole;
use App\Entity\HyvorTalkWebsite;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use App\Service\Integration\HyvorTalk\SyncBlogUsersToWebsiteMessage;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\InterHyvorTalkWebsiteFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Helper\Fixtures;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Sdk\Auth\StaticTokenProvider;
use Hyvor\Sdk\HyvorClient;
use Hyvor\Sdk\Talk\Dto\Mod;
use PHPUnit\Framework\Attributes\CoversClass;
use Sentry\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(HyvorTalkController::class)]
#[CoversClass(HyvorTalkService::class)]
class ConnectHyvorTalkIntegrationTest extends ApiTestCase
{
    public function test_connects_and_persists_the_integration(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-connect'], ['hyvor_user_id' => 543, 'role' => UserRole::ADMIN]);
        LanguageFactory::createOnePrimaryFor($blog);
        BlogVariantFactory::createManyForBlogWithAllLanguages($blog, attributes: ['name' => 'My Blog']);

        $htWebsiteResponse = new JsonMockResponse(['id' => 123]);
        $htModResponse = new JsonMockResponse(Fixtures::make(Mod::class, ['role' => 'admin']));
        $mockClient = new MockHttpClient([$htWebsiteResponse, $htModResponse]);
        $this->getContainer()->set(HttpClientInterface::class, $mockClient);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new HyvorClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->getContainer()->set(CloudApiService::class, $cloudApiServiceMock);

        $response = $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-talk/connect', user: $owner);

        $hyvorTalk = $this->getEm()->getRepository(HyvorTalkWebsite::class)->findOneBy(['blog' => $blog]);
        $this->assertNotNull($hyvorTalk);
        $this->assertTrue($hyvorTalk->isCreatedByBlogs());
        $this->assertSame(123, $hyvorTalk->getWebsiteId());

        $requestBody = json_decode($htWebsiteResponse->getRequestOptions()['body'], true);
        $this->assertSame('My Blog', $requestBody['name']);
        $this->assertSame('true', $requestBody['metadata']['hyvor_blogs_integration']);
        $this->assertSame((string) $blog->getId(), $requestBody['metadata']['hyvor_blogs_blog_id']);

        $modRequestBody = json_decode($htModResponse->getRequestOptions()['body'], true);
        $this->assertSame(543, $modRequestBody['user_id']);
        $this->assertSame('admin', $modRequestBody['role']);
        $this->assertSame('ignore', $modRequestBody['on_duplicate']);

        $transport = $this->transport('async')->throwExceptions();
        $messages = $transport->queue()->messages(SyncBlogUsersToWebsiteMessage::class);
        $this->assertCount(1, $messages);
        $message = $messages[0];
        $this->assertSame($blog->getId(), $message->blogId);
    }

    public function test_conflicts_when_already_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'ht-connect-conflict']);
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-talk/connect', user: $owner);

        $this->assertResponseFailed(422, 'This blog is already connected to Hyvor Talk');
    }

}
