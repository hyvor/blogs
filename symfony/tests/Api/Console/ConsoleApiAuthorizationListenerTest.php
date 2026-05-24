<?php

namespace Api\Console;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Controller\ApiKeyController;
use App\Api\Console\ControllerOrg\ConsoleController;
use App\Tests\Case\ApiTestCase;
use App\Entity\Enum\UserRole;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ConsoleApiAuthorizationListener::class)]
#[UsesClass(ConsoleController::class)]
#[UsesClass(ApiKeyController::class)]
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
        $user = AuthFake::generateUser();
        $this->consoleOrgApi('GET', '/init', user: $user);

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

        $this->assertResponseStatusCodeSame(403);
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

        $this->assertResponseStatusCodeSame(403);
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

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_blog_level_returns_401_when_not_authenticated(): void
    {
        BlogFactory::createOne(['subdomain' => 'auth-test-blog']);

        $this->consoleBlogApi('GET', 'auth-test-blog', '/api-keys');

        $this->assertResponseStatusCodeSame(401);
    }

    public function test_blog_level_returns_403_when_user_not_in_blog(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'auth-test-other'],
            ['hyvor_user_id' => 301, 'status' => 'active'],
        );

        $otherUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'auth-test-other', '/api-keys', user: $otherUser);

        $this->assertResponseStatusCodeSame(403);
    }

    // -----------------------------------------------------------------------
    // Blog-level: API key auth
    // -----------------------------------------------------------------------

    public function test_api_key_auth_succeeds(): void
    {
        [$blog] = BlogFactory::createOneWithUser(
            ['subdomain' => 'apikey-auth-blog'],
            ['hyvor_user_id' => 400, 'status' => 'active', 'role' => UserRole::OWNER],
        );

        ApiKeyFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'api_key' => 'validrawkey123456789012345678901',
            'type' => 'console',
            'name' => 'Test Key',
        ]);

        $this->consoleBlogApi(
            'GET',
            'apikey-auth-blog',
            '/api-keys',
            server: ['HTTP_AUTHORIZATION' => 'Bearer validrawkey123456789012345678901'],
        );

        $this->assertResponseIsSuccessful();
    }

    public function test_api_key_auth_returns_403_for_invalid_key(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'apikey-invalid-blog'],
            ['hyvor_user_id' => 401, 'status' => 'active'],
        );

        $this->consoleBlogApi(
            'GET',
            'apikey-invalid-blog',
            '/api-keys',
            server: ['HTTP_AUTHORIZATION' => 'Bearer wrongkey'],
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_api_key_auth_returns_403_for_wrong_bearer_format(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'apikey-format-blog'],
            ['hyvor_user_id' => 402, 'status' => 'active'],
        );

        $this->consoleBlogApi(
            'GET',
            'apikey-format-blog',
            '/api-keys',
            server: ['HTTP_AUTHORIZATION' => 'Basic somekey'],
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_delivery_type_key_cannot_be_used_for_api_key_auth(): void
    {
        [$blog] = BlogFactory::createOneWithUser(
            ['subdomain' => 'apikey-delivery-blog'],
            ['hyvor_user_id' => 403, 'status' => 'active'],
        );

        ApiKeyFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'api_key' => 'deliverykey123456789012345678901',
            'type' => 'delivery',
            'name' => 'Delivery Key',
        ]);

        $this->consoleBlogApi(
            'GET',
            'apikey-delivery-blog',
            '/api-keys',
            server: ['HTTP_AUTHORIZATION' => 'Bearer deliverykey123456789012345678901'],
        );

        $this->assertResponseStatusCodeSame(403);
    }

}
