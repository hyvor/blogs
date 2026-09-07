<?php

namespace App\Service\App\Router;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CaddyRouterHeaderListener
{

    #[AsEventListener]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $router = $_ENV['CADDY_ROUTER'] ?? 'app';
        $event->getResponse()->headers->set('X-Hb-Router', is_string($router) ? $router : 'app');
    }
}
