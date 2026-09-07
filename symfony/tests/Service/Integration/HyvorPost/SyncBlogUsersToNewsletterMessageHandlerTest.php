<?php

namespace App\Tests\Service\Integration\HyvorPost;

use App\Entity\Enum\UserRole;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Service\Integration\HyvorPost\SyncBlogUsersToNewsletterMessage;
use App\Service\Integration\HyvorPost\SyncBlogUsersToNewsletterMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Helper\Fixtures;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Sdk\Auth\StaticTokenProvider;
use Hyvor\Sdk\Post\Dto\User;
use Hyvor\Sdk\Post\Dto\UserMini;
use Hyvor\Sdk\Post\PostClient;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(SyncBlogUsersToNewsletterMessageHandler::class)]
#[CoversClass(SyncBlogUsersToNewsletterMessage::class)]
#[CoversClass(HyvorPostService::class)]
class SyncBlogUsersToNewsletterMessageHandlerTest extends KernelTestCase
{

    // 1. Sync all

    public function test_syncs_only_admin_and_editor_users_with_correct_request(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 555]);
        $hyvorPost = HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 777]);

        $admin = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN, 'hyvor_user_id' => 1001]);
        $editor = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR, 'hyvor_user_id' => 1002]);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER, 'hyvor_user_id' => 1003]);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::CONTRIBUTOR, 'hyvor_user_id' => 1004]);

        // other user
        UserFactory::createOne(['role' => UserRole::ADMIN, 'hyvor_user_id' => 1005]);

        $requests = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url, array $options) use (&$requests): JsonMockResponse {
                $body = $options['body'];
                $this->assertIsString($body);
                $decodedBody = json_decode($body, true);
                $this->assertIsArray($decodedBody);
                $headers = $options['normalized_headers'];
                $this->assertIsArray($headers);

                $requests[] = [
                    'method' => $method,
                    'url' => $url,
                    'body' => $decodedBody,
                    'headers' => $headers,
                ];

                return new JsonMockResponse(Fixtures::make(User::class, [
                    'id' => 1,
                    'role' => 'admin',
                    'user' => Fixtures::make(UserMini::class, ['name' => 'Test', 'email' => 'test@example.com']),
                ]));
            }
        );

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new PostClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncBlogUsersToNewsletterMessage($blog->getId()));
        $transport->processOrFail(1);

        $this->assertCount(2, $requests);

        foreach ($requests as $request) {
            $this->assertSame('POST', $request['method']);
            $this->assertStringContainsString('/api/console/users', $request['url']);
            $this->assertSame('ignore', $request['body']['on_duplicate']);
            $newsletterIdHeader = $request['headers']['x-newsletter-id'];
            $this->assertIsArray($newsletterIdHeader);
            $this->assertSame('X-Newsletter-Id: ' . $hyvorPost->getNewsletterId(), $newsletterIdHeader[0]);
        }

        $this->assertSame($admin->getHyvorUserId(), $requests[0]['body']['user_id']);
        $this->assertSame($editor->getHyvorUserId(), $requests[1]['body']['user_id']);
    }

    public function test_does_nothing_when_blog_not_connected_to_hyvor_post(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 555]);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willThrowException(new \RuntimeException('should not be called'));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncBlogUsersToNewsletterMessage($blog->getId()));
        $transport->processOrFail(1);

        $this->addToAssertionCount(1);
    }

    // 2. Sync single user

    public function test_syncs_single_user_with_correct_request(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 555]);
        $hyvorPost = HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 777]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN, 'hyvor_user_id' => 1001]);
        $otherUser = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR, 'hyvor_user_id' => 1002]);

        $requests = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url, array $options) use (&$requests): JsonMockResponse {
                $body = $options['body'];
                $this->assertIsString($body);
                $decodedBody = json_decode($body, true);
                $this->assertIsArray($decodedBody);
                $headers = $options['normalized_headers'];
                $this->assertIsArray($headers);

                $requests[] = [
                    'method' => $method,
                    'url' => $url,
                    'body' => $decodedBody,
                    'headers' => $headers,
                ];

                return new JsonMockResponse(Fixtures::make(User::class, [
                    'id' => 1,
                    'role' => 'admin',
                    'user' => Fixtures::make(UserMini::class, ['name' => 'Test', 'email' => 'test@example.com']),
                ]));
            }
        );

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new PostClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncBlogUsersToNewsletterMessage($blog->getId(), $user->getHyvorUserId()));
        $transport->processOrFail(1);

        $this->assertCount(1, $requests);

        $request = $requests[0];
        $this->assertSame('POST', $request['method']);
        $this->assertStringContainsString('/api/console/users', $request['url']);
        $this->assertSame('ignore', $request['body']['on_duplicate']);
        $this->assertSame($user->getHyvorUserId(), $request['body']['user_id']);
        $newsletterIdHeader = $request['headers']['x-newsletter-id'];
        $this->assertIsArray($newsletterIdHeader);
        $this->assertSame('X-Newsletter-Id: ' . $hyvorPost->getNewsletterId(), $newsletterIdHeader[0]);
    }

    // 3. Delete single user

    public function test_deletes_single_user_with_correct_request(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 555]);
        $hyvorPost = HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 777]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN, 'hyvor_user_id' => 1001]);

        $requests = [];

        $mockClient = new MockHttpClient(
            function (string $method, string $url, array $options) use (&$requests): JsonMockResponse {
                $body = $options['body'];
                $this->assertIsString($body);
                $decodedBody = json_decode($body, true);
                $this->assertIsArray($decodedBody);
                $headers = $options['normalized_headers'];
                $this->assertIsArray($headers);

                $requests[] = [
                    'method' => $method,
                    'url' => $url,
                    'body' => $decodedBody,
                    'headers' => $headers,
                ];

                return new JsonMockResponse([]);
            }
        );

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new PostClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->container->set(CloudApiService::class, $cloudApiServiceMock);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SyncBlogUsersToNewsletterMessage($blog->getId(), $user->getHyvorUserId(), delete: true));
        $transport->processOrFail(1);

        $this->assertCount(1, $requests);

        $request = $requests[0];
        $this->assertSame('DELETE', $request['method']);
        $this->assertStringContainsString('/api/console/users', $request['url']);
        $this->assertSame($user->getHyvorUserId(), $request['body']['user_id']);
        $newsletterIdHeader = $request['headers']['x-newsletter-id'];
        $this->assertIsArray($newsletterIdHeader);
        $this->assertSame('X-Newsletter-Id: ' . $hyvorPost->getNewsletterId(), $newsletterIdHeader[0]);
    }

}
