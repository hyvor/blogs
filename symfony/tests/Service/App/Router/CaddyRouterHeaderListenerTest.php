<?php

namespace App\Tests\Service\App\Router;

use App\Service\App\Router\CaddyRouterHeaderListener;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(CaddyRouterHeaderListener::class)]
class CaddyRouterHeaderListenerTest extends KernelTestCase
{

    protected function tearDown(): void
    {
        unset($_ENV['CADDY_ROUTER']);
    }

    private function dispatch(): Response
    {
        $listener = $this->getService(CaddyRouterHeaderListener::class);
        $response = new Response();
        $event = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/'),
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $listener->onKernelResponse($event);

        return $response;
    }

    public function test_sets_router_header_from_env(): void
    {
        $_ENV['CADDY_ROUTER'] = 'my-router';

        $response = $this->dispatch();

        $this->assertSame('my-router / 0.0.0', $response->headers->get('X-Hb-Server'));
    }

    public function test_defaults_to_app_when_env_not_set(): void
    {
        unset($_ENV['CADDY_ROUTER']);

        $response = $this->dispatch();

        $this->assertSame('app / 0.0.0', $response->headers->get('X-Hb-Server'));
    }

}
