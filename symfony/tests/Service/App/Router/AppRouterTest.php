<?php

namespace App\Tests\Service\App\Router;

use App\Service\App\Router\AppRouter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(AppRouter::class)]
class AppRouterTest extends TestCase
{
    private ?string $originalCaddyRouter;

    protected function setUp(): void
    {
        $caddyRouter = $_ENV['CADDY_ROUTER'] ?? null;
        $this->originalCaddyRouter = is_string($caddyRouter) ? $caddyRouter : null;
    }

    protected function tearDown(): void
    {
        if ($this->originalCaddyRouter === null) {
            unset($_ENV['CADDY_ROUTER']);
        } else {
            $_ENV['CADDY_ROUTER'] = $this->originalCaddyRouter;
        }
    }

    private function appRouter(?string $caddyRouter, bool $isSubrequest = false): AppRouter
    {
        if ($caddyRouter === null) {
            unset($_ENV['CADDY_ROUTER']);
        } else {
            $_ENV['CADDY_ROUTER'] = $caddyRouter;
        }

        $mainRequest = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($mainRequest);

        if ($isSubrequest) {
            $requestStack->push(new Request());
        }

        return new AppRouter($requestStack);
    }

    // -----------------------------------------------------------------------
    // CADDY_ROUTER = app
    // -----------------------------------------------------------------------

    public function test_routes_to_app(): void
    {
        $appRouter = $this->appRouter('app');

        $this->assertTrue($appRouter->isApp());
        $this->assertFalse($appRouter->isSubdomain());
        $this->assertFalse($appRouter->isCustomDomain());
        $this->assertFalse($appRouter->isLocal());
    }

    // -----------------------------------------------------------------------
    // CADDY_ROUTER = subdomain
    // -----------------------------------------------------------------------

    public function test_routes_to_subdomain(): void
    {
        $appRouter = $this->appRouter('subdomain');

        $this->assertFalse($appRouter->isApp());
        $this->assertTrue($appRouter->isSubdomain());
        $this->assertFalse($appRouter->isCustomDomain());
        $this->assertFalse($appRouter->isLocal());
    }

    // -----------------------------------------------------------------------
    // CADDY_ROUTER = customdomain
    // -----------------------------------------------------------------------

    public function test_routes_to_customdomain(): void
    {
        $appRouter = $this->appRouter('customdomain');

        $this->assertFalse($appRouter->isApp());
        $this->assertFalse($appRouter->isSubdomain());
        $this->assertTrue($appRouter->isCustomDomain());
        $this->assertFalse($appRouter->isLocal());
    }

    // -----------------------------------------------------------------------
    // CADDY_ROUTER = local
    // -----------------------------------------------------------------------

    public function test_routes_to_local(): void
    {
        $appRouter = $this->appRouter('local');

        $this->assertFalse($appRouter->isApp());
        $this->assertFalse($appRouter->isSubdomain());
        $this->assertFalse($appRouter->isCustomDomain());
        $this->assertTrue($appRouter->isLocal());
    }

    // -----------------------------------------------------------------------
    // Defaults
    // -----------------------------------------------------------------------

    public function test_defaults_to_app_when_caddy_router_env_is_not_set(): void
    {
        $appRouter = $this->appRouter(null);

        $this->assertTrue($appRouter->isApp());
    }

    // -----------------------------------------------------------------------
    // Subrequests always route to app
    // -----------------------------------------------------------------------

    public function test_subrequest_always_routes_to_app_regardless_of_caddy_router(): void
    {
        $appRouter = $this->appRouter('subdomain', isSubrequest: true);

        $this->assertTrue($appRouter->isApp());
        $this->assertFalse($appRouter->isSubdomain());
    }

    public function test_main_request_uses_caddy_router_when_not_a_subrequest(): void
    {
        $appRouter = $this->appRouter('customdomain', isSubrequest: false);

        $this->assertFalse($appRouter->isApp());
        $this->assertTrue($appRouter->isCustomDomain());
    }
}
