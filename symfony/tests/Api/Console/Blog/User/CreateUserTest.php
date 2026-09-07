<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Entity\User as BlogUser;
use App\Service\Integration\HyvorPost\SyncBlogUsersToNewsletterMessage;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMember;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMemberResponse;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\Deployment;
use Hyvor\Sdk\Auth\StaticTokenProvider;
use Hyvor\Sdk\Post\PostClient;
use PHPUnit\Framework\Attributes\CoversClass;
use Sentry\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserCreatedEvent::class)]
class CreateUserTest extends ApiTestCase
{
    /**
     * consoleBlogApi() resets AuthFake on every call (no usersDatabase support),
     * which would wipe out the target hyvor user we want AuthInterface::fromId()
     * to resolve. So we replicate it here with a usersDatabase.
     *
     * @param array<string, mixed> $data
     */
    private function requestAsBlogUser(
        BlogUser $owner,
        AuthUser $hyvorUser,
        string $method,
        string $endpoint,
        array $data,
    ): Response {
        $blog = $owner->getBlog();
        $orgId = $blog->getOrganizationId() ?? 0;
        $ownerAuthUser = AuthFake::generateUser(['id' => (int)$owner->getHyvorUserId()]);

        AuthFake::enableForSymfony(
            $this->getContainer(),
            $ownerAuthUser,
            new AuthUserOrganization($orgId, '', 'admin'),
            usersDatabase: [$ownerAuthUser, $hyvorUser],
        );

        $this->client->request(
            $method,
            '/api/console/v0/blog/' . $blog->getSubdomain() . '/' . ltrim($endpoint, '/'),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_ORGANIZATION_ID' => $orgId],
            content: (string)json_encode($data),
        );

        return $this->client->getResponse();
    }

    /**
     * Deployment in the test env defaults to cloud (see symfony/.env.test), so
     * the cloud-only VerifyMember comms check runs on every request here.
     */
    private function setUpComms(): void
    {
        $this->getComms()->addResponse(VerifyMember::class, fn() => new VerifyMemberResponse(true, 'member'));
    }

    private function enableBilling(int $organizationId, int $usersLimit = 2): void
    {
        $license = BlogsLicense::trial();
        $license->users = $usersLimit;
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [$organizationId => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)],
        );
    }

    /**
     * Replaces the Hyvor Post SDK's HTTP transport with a MockHttpClient so
     * HyvorPostService::addUser() runs for real against queued responses,
     * instead of swapping out HyvorPostService itself.
     *
     * @param JsonMockResponse[] $responses
     */
    private function mockHyvorPostHttpClient(array $responses): MockHttpClient
    {
        $mockClient = new MockHttpClient($responses);
        $this->getContainer()->set(HttpClientInterface::class, $mockClient);

        $cloudApiServiceMock = $this->createStub(CloudApiService::class);
        $cloudApiServiceMock->method('getHyvorClientForOrganization')
            ->willReturn(new PostClient(tokenProvider: new StaticTokenProvider('fake-jwt-token'), httpClient: new Psr18Client($mockClient)));
        $this->getContainer()->set(CloudApiService::class, $cloudApiServiceMock);

        return $mockClient;
    }

    public function test_creates_a_user(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user', 'organization_id' => 3001]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(
            id: 1239,
            username: 'hyvor',
            name: 'HYVOR Company',
            email: 'hyvor@hyvor.com',
            picture_url: null,
            location: 'France',
            bio: 'Building SaaS products',
            website_url: 'https://hyvor.com',
        );
        $this->setUpComms();
        $this->enableBilling(3001);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('hyvor@hyvor.com', $json['email']);
        $this->assertSame('admin', $json['role']);
        $this->assertSame('https://hyvor.com', $json['website_url']);
        $this->assertIsArray($json['variants']);
        $this->assertIsArray($json['variants'][0]);
        $this->assertSame('HYVOR Company', $json['variants'][0]['name']);
        $this->assertSame('Building SaaS products', $json['variants'][0]['bio']);
        $this->assertSame('France', $json['variants'][0]['location']);

        $this->getEd()->assertDispatched(UserCreatedEvent::class);
    }

    public function test_does_not_create_if_user_exists(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user-exists', 'organization_id' => 3003]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(id: 1241, username: 'user1241', name: 'User', email: 'user1241@example.com');
        $this->setUpComms();
        $this->enableBilling(3003, usersLimit: 5);

        UserFactory::createOne(['blog' => $blog, 'hyvor_user_id' => $hyvorUser->id]);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'User is already added to the blog',
            (string)$this->client->getResponse()->getContent(),
        );
    }

    public function test_fails_when_limits_exceeded(): void
    {
        $blog = BlogFactory::createOne([
            'subdomain' => 'create-user-limit',
            'organization_id' => 3004,
        ]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog, 'hyvor_user_id' => 1001]);
        UserFactory::createOne(['blog' => $blog, 'hyvor_user_id' => 1002]);
        $hyvorUser = new AuthUser(id: 1242, username: 'user1242', name: 'User', email: 'user1242@example.com');
        $this->setUpComms();
        $this->enableBilling(3004);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $message = $this->getJson()['message'];
        $this->assertIsString($message);
        $this->assertStringContainsString(
            'Max users limit exceeded. Please upgrade your organization\'s Hyvor Blogs plan',
            $message,
        );
    }

    public function test_creates_hyvor_post_user_for_admin_role(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user-hp-admin', 'organization_id' => 3010]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(id: 1250, username: 'hpuser1250', name: 'HP User', email: 'hpuser1250@example.com');
        $this->setUpComms();
        $this->enableBilling(3010);
        HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 4242]);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseIsSuccessful();

        $messages = $this->transport('async')->queue()->messages(SyncBlogUsersToNewsletterMessage::class);
        $this->assertCount(1, $messages);
        $this->assertSame($blog->getId(), $messages[0]->blogId);
        $this->assertSame(1250, $messages[0]->hyvorUserId);
        $this->assertFalse($messages[0]->delete);
    }

    public function test_creates_hyvor_post_user_for_editor_role(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user-hp-editor', 'organization_id' => 3011]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(id: 1251, username: 'hpuser1251', name: 'HP User', email: 'hpuser1251@example.com');
        $this->setUpComms();
        $this->enableBilling(3011);
        HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 4243]);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'editor',
        ]);

        $this->assertResponseIsSuccessful();

        $messages = $this->transport('async')->queue()->messages(SyncBlogUsersToNewsletterMessage::class);
        $this->assertCount(1, $messages);
        $this->assertSame($blog->getId(), $messages[0]->blogId);
        $this->assertSame(1251, $messages[0]->hyvorUserId);
        $this->assertFalse($messages[0]->delete);
    }

    public function test_does_not_create_hyvor_post_user_for_writer_role(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user-hp-writer', 'organization_id' => 3012]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(id: 1252, username: 'hpuser1252', name: 'HP User', email: 'hpuser1252@example.com');
        $this->setUpComms();
        $this->enableBilling(3012);
        HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 4244]);

        $mockClient = $this->mockHyvorPostHttpClient([]);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'writer',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $mockClient->getRequestsCount());
    }

    public function test_does_not_create_hyvor_post_user_when_not_connected(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user-hp-unconnected', 'organization_id' => 3013]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(id: 1253, username: 'hpuser1253', name: 'HP User', email: 'hpuser1253@example.com');
        $this->setUpComms();
        $this->enableBilling(3013);
        // note: no HyvorPostFactory row created, so the blog is not connected

        $mockClient = $this->mockHyvorPostHttpClient([]);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $mockClient->getRequestsCount());
    }

    public function test_does_not_create_hyvor_post_user_on_prem(): void
    {
        $this->setEnvVar('DEPLOYMENT', Deployment::ON_PREM->value);

        $blog = BlogFactory::createOne(['subdomain' => 'create-user-hp-onprem', 'organization_id' => 3014]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(id: 1254, username: 'hpuser1254', name: 'HP User', email: 'hpuser1254@example.com');
        HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 4245]);

        $mockClient = $this->mockHyvorPostHttpClient([]);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $mockClient->getRequestsCount());
    }

    public function test_user_creation_is_not_blocked_by_hyvor_post_sync(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user-hp-rollback', 'organization_id' => 3015]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(id: 1255, username: 'hpuser1255', name: 'HP User', email: 'hpuser1255@example.com');
        $this->setUpComms();
        $this->enableBilling(3015);
        HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 4246]);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertNotNull(
            $this->getEm()->getRepository(BlogUser::class)->findOneBy(['blog' => $blog, 'hyvor_user_id' => 1255]),
        );

        $messages = $this->transport('async')->queue()->messages(SyncBlogUsersToNewsletterMessage::class);
        $this->assertCount(1, $messages);
        $this->assertSame($blog->getId(), $messages[0]->blogId);
        $this->assertSame(1255, $messages[0]->hyvorUserId);
    }
}
