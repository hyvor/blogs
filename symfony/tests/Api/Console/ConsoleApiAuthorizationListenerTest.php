<?php

namespace Api\Console;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\ControllerOrg\ConsoleController;
use App\Entity\Enum\ApiKeyType;
use App\Entity\Enum\UserStatus;
use App\Service\ApiKey\ApiKeyService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ConsoleApiAuthorizationListener::class)]
#[UsesClass(ConsoleController::class)]
#[CoversClass(ApiKeyService::class)]
class ConsoleApiAuthorizationListenerTest extends ApiTestCase
{

    // -----------------------------------------------------------------------
    // Unauthenticated (org-level)
    // -----------------------------------------------------------------------

    public function test_returns_401_if_not_authenticated(): void
    {
        $this->consoleOrgApi('GET', '/init');
        $this->assertResponseStatusCodeSame(401);
        $json = $this->getJson();
        $this->assertIsArray($json['data']);
        $this->assertArrayHasKey('login_url', $json['data']);
        $this->assertArrayHasKey('signup_url', $json['data']);
    }

    // -----------------------------------------------------------------------
    // OrganizationOptional path — via /init (#[OrganizationLevelEndpoint] + #[OrganizationOptional])
    // Listener returns early after setting attributes; org header is never checked.
    // -----------------------------------------------------------------------

    public function test_org_optional_passes_without_org(): void
    {
        $this->consoleOrgApi('GET', '/init', user: 1, organization: null);

        $this->assertResponseIsSuccessful();
        $this->assertNull($this->getJson()['organization']);
    }

    public function test_org_optional_passes_with_org(): void
    {
        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization(42, 'Test Org', 'admin');
        $this->consoleOrgApi('GET', '/init', user: $user, organization: $org);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['organization']);
        $this->assertSame(42, $json['organization']['id']);
    }

    public function test_org_optional_passes_even_when_org_header_is_wrong(): void
    {
        // Header mismatch is irrelevant when the endpoint is org-optional.
        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization(42, 'Test Org', 'admin');
        $this->consoleOrgApi(
            'GET',
            '/init',
            server: ['HTTP_X_ORGANIZATION_ID' => "999"],
            user: $user,
            organization: $org,
            setOrgHeader: false,
        );

        $this->assertResponseIsSuccessful();
    }

    // -----------------------------------------------------------------------
    // Org-required path — via /ping (#[OrganizationLevelEndpoint] only)
    // Listener does NOT return early; org presence and header match are enforced.
    // -----------------------------------------------------------------------

    public function test_org_required_returns_403_when_no_org(): void
    {
        $user = AuthFake::generateUser();
        $this->consoleOrgApi('GET', '/ping', user: $user);

        $this->assertResponseFailed(403, 'Organization is required');
    }

    public function test_org_required_returns_403_on_header_mismatch(): void
    {
        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization(42, 'Test Org', 'admin');
        $this->consoleOrgApi(
            'GET',
            '/ping',
            server: ['HTTP_X_ORGANIZATION_ID' => "999"],
            user: $user,
            organization: $org,
            setOrgHeader: false,
        );

        $this->assertResponseFailed(403, 'org_mismatch');
    }

    public function test_org_required_passes_with_correct_header(): void
    {
        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization(42, 'Test Org', 'admin');
        $this->consoleOrgApi('GET', '/ping', user: $user, organization: $org);

        $this->assertResponseIsSuccessful();
    }

    // -----------------------------------------------------------------------
    // Blog-level: session auth
    // -----------------------------------------------------------------------

    public function test_blog_level_returns_404_when_subdomain_not_found(): void
    {
        $user = AuthFake::generateUser(['id' => 300]);
        $this->consoleBlogApi('GET', 'nonexistent-blog', '/api-keys', user: $user);

        $this->assertResponseFailed(404, 'Blog not found');
    }

    public function test_blog_level_returns_401_when_not_authenticated(): void
    {
        BlogFactory::createOne(['subdomain' => 'auth-test-blog']);

        $this->consoleBlogApi('GET', 'auth-test-blog', '/api-keys');

        $this->assertResponseFailed(401, 'Unauthorized');
    }

    public function test_blog_level_returns_403_when_no_org(): void
    {
        BlogFactory::createOneWithUser(['subdomain' => 'auth-no-org'], ['status' => UserStatus::ACTIVE]);

        // Use the client directly — consoleBlogApi auto-wires org from the blog,
        // but this test needs the user to have NO org to trigger the 403.
        $user = AuthFake::generateUser();
        AuthFake::enableForSymfony($this->getContainer(), $user, null);
        $this->client->request(
            'GET',
            '/api/console/v0/blog/auth-no-org/api-keys',
            server: ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseFailed(403, 'Organization is required');
    }

    public function test_blog_level_returns_403_when_org_does_not_match_blog(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'auth-org-mismatch', 'organization_id' => 100],
            ['status' => UserStatus::ACTIVE],
        );

        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization(999, 'Other Org', 'admin');
        AuthFake::enableForSymfony($this->getContainer(), $user, $org);
        $this->client->request(
            'GET',
            '/api/console/v0/blog/auth-org-mismatch/api-keys',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_ORGANIZATION_ID' => '999'],
        );

        $this->assertResponseFailed(403, 'This project does not belong to your current organization.');
    }

    public function test_blog_level_returns_403_on_org_header_mismatch(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'auth-hdr-mismatch', 'organization_id' => 200],
            ['status' => UserStatus::ACTIVE],
        );

        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization(200, 'Org', 'admin');
        AuthFake::enableForSymfony($this->getContainer(), $user, $org);
        $this->client->request(
            'GET',
            '/api/console/v0/blog/auth-hdr-mismatch/api-keys',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_ORGANIZATION_ID' => '999'],
        );

        $this->assertResponseFailed(403, 'org_mismatch');
    }

    public function test_blog_level_returns_403_when_user_not_in_blog(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'auth-test-other', 'organization_id' => 300],
            ['hyvor_user_id' => 301, 'status' => UserStatus::ACTIVE],
        );

        $otherUser = AuthFake::generateUser(['id' => 999]);
        $org = new AuthUserOrganization(300, 'Org', 'admin');
        AuthFake::enableForSymfony($this->getContainer(), $otherUser, $org);
        $this->client->request(
            'GET',
            '/api/console/v0/blog/auth-test-other/api-keys',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_ORGANIZATION_ID' => '300'],
        );

        $this->assertResponseFailed(403, 'You do not have access to this blog');
    }

    // -----------------------------------------------------------------------
    // Blog-level: API key auth (X-API-Key header)
    // -----------------------------------------------------------------------

    public function test_api_key_auth_succeeds(): void
    {
        [$blog] = BlogFactory::createOneWithUser(
            ['subdomain' => 'apikey-auth-blog'],
            ['hyvor_user_id' => 400, 'status' => UserStatus::ACTIVE],
        );

        ApiKeyFactory::createOne([
            'blog' => $blog,
            'api_key' => 'validrawkey123456789012345678901',
            'type' => ApiKeyType::CONSOLE,
            'name' => 'Test Key',
        ]);

        $this->consoleBlogApi(
            'GET',
            'apikey-auth-blog',
            '/api-keys',
            server: ['HTTP_X_API_KEY' => 'validrawkey123456789012345678901'],
        );

        $this->assertResponseIsSuccessful();
    }

    public function test_api_key_auth_returns_403_for_invalid_key(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'apikey-invalid-blog'],
            ['hyvor_user_id' => 401, 'status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi(
            'GET',
            'apikey-invalid-blog',
            '/api-keys',
            server: ['HTTP_X_API_KEY' => 'wrongkey'],
        );

        $this->assertResponseFailed(403, 'Invalid API key');
    }

    public function test_delivery_type_key_cannot_be_used_for_api_key_auth(): void
    {
        [$blog] = BlogFactory::createOneWithUser(
            ['subdomain' => 'apikey-delivery-blog'],
            ['hyvor_user_id' => 402, 'status' => UserStatus::ACTIVE],
        );

        ApiKeyFactory::createOne([
            'blog' => $blog,
            'api_key' => 'deliverykey123456789012345678901',
            'type' => ApiKeyType::DELIVERY,
            'name' => 'Delivery Key',
        ]);

        $this->consoleBlogApi(
            'GET',
            'apikey-delivery-blog',
            '/api-keys',
            server: ['HTTP_X_API_KEY' => 'deliverykey123456789012345678901'],
        );

        $this->assertResponseFailed(403, 'Invalid API key');
    }
}
