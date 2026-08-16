<?php

namespace App\Tests\Service\Integration\HyvorTalk;

use App\Entity\Enum\UserRole;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use App\Service\Integration\HyvorTalk\SyncBlogUsersToWebsiteMessage;
use App\Service\Integration\HyvorTalk\SyncBlogUsersToWebsiteMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\InterHyvorTalkWebsiteFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Helper\Fixtures;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Sdk\Auth\StaticTokenProvider;
use Hyvor\Sdk\Talk\Dto\Mod;
use Hyvor\Sdk\Talk\TalkClient;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(SyncBlogUsersToWebsiteMessageHandler::class)]
#[CoversClass(SyncBlogUsersToWebsiteMessage::class)]
#[CoversClass(HyvorTalkService::class)]
class SyncBlogUsersToWebsiteMessageHandlerTest extends KernelTestCase
{

    // 1. Sync all

    public function test_syncs_only_admin_and_editor_users_with_correct_roles(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 555]);
        $hyvorTalk = InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 777]);

        $admin = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN, 'hyvor_user_id' => 1001]);
        $editor = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR, 'hyvor_user_id' => 1002]);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER, 'hyvor_user_id' => 1003]);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::CONTRIBUTOR, 'hyvor_user_id' => 1004]);

        // other user
        UserFactory::createOne(['role' => UserRole::ADMIN, 'hyvor_user_id' => 1005]);

        $requests = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url, array $options) use (&$requests): JsonMockResponse {
                $requests[] = [
                    'method' => $method,
                    'url' => $url,
                    'body' => json_decode($options['body'], true),
                    'headers' => $options['normalized_headers'],
                ];

                return new JsonMockResponse(Fixtures::make(Mod::class, ['role' => 'admin']));
            }
        );

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new TalkClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncBlogUsersToWebsiteMessage($blog->getId()));
        $transport->processOrFail(1);

        $this->assertCount(2, $requests);

        foreach ($requests as $request) {
            $this->assertSame('POST', $request['method']);
            $this->assertStringContainsString('/mods', $request['url']);
            $this->assertSame('ignore', $request['body']['on_duplicate']);
        }

        $this->assertSame($admin->getHyvorUserId(), $requests[0]['body']['user_id']);
        $this->assertSame('admin', $requests[0]['body']['role']);
        $this->assertSame($editor->getHyvorUserId(), $requests[1]['body']['user_id']);
        $this->assertSame('mod', $requests[1]['body']['role']);
    }

    public function test_does_nothing_when_blog_not_connected_to_hyvor_talk(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 555]);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willThrowException(new \RuntimeException('should not be called'));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncBlogUsersToWebsiteMessage($blog->getId()));
        $transport->processOrFail(1);

        $this->addToAssertionCount(1);
    }

    // 2. Sync single user

    public function test_syncs_single_user_with_correct_role(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 555]);
        $hyvorTalk = InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 777]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR, 'hyvor_user_id' => 1001]);

        $requests = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url, array $options) use (&$requests): JsonMockResponse {
                $requests[] = [
                    'method' => $method,
                    'url' => $url,
                    'body' => json_decode($options['body'], true),
                    'headers' => $options['normalized_headers'],
                ];

                return new JsonMockResponse(Fixtures::make(Mod::class, ['role' => 'mod']));
            }
        );

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new TalkClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncBlogUsersToWebsiteMessage($blog->getId(), $user->getHyvorUserId(), role: 'mod'));
        $transport->processOrFail(1);

        $this->assertCount(1, $requests);

        $request = $requests[0];
        $this->assertSame('POST', $request['method']);
        $this->assertStringContainsString('/mods', $request['url']);
        $this->assertSame('ignore', $request['body']['on_duplicate']);
        $this->assertSame($user->getHyvorUserId(), $request['body']['user_id']);
        $this->assertSame('mod', $request['body']['role']);
    }

    // 3. Delete single user

    public function test_deletes_single_user_with_correct_request(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 555]);
        $hyvorTalk = InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 777]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN, 'hyvor_user_id' => 1001]);

        $requests = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url, array $options) use (&$requests): JsonMockResponse {
                $requests[] = [
                    'method' => $method,
                    'url' => $url,
                    'body' => json_decode($options['body'], true),
                    'headers' => $options['normalized_headers'],
                ];

                return new JsonMockResponse([]);
            }
        );

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new TalkClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncBlogUsersToWebsiteMessage($blog->getId(), $user->getHyvorUserId(), delete: true));
        $transport->processOrFail(1);

        $this->assertCount(1, $requests);

        $request = $requests[0];
        $this->assertSame('DELETE', $request['method']);
        $this->assertStringContainsString('/mods', $request['url']);
        $this->assertSame($user->getHyvorUserId(), $request['body']['user_id']);
    }

}
