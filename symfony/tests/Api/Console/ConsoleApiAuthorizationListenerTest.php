<?php

namespace Api\Console;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Controller\ConsoleController;
use App\Tests\Case\ApiTestCase;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ConsoleApiAuthorizationListener::class)]
#[UsesClass(ConsoleController::class)]
class ConsoleApiAuthorizationListenerTest extends ApiTestCase
{

    // -----------------------------------------------------------------------
    // Unauthenticated
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

}
