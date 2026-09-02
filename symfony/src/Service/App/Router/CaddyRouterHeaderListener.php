<?php

namespace App\Service\App\Router;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CaddyRouterHeaderListener
{

    #[AsEventListener]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set('X-Caddy-Router', $_ENV['CADDY_ROUTER'] ?? 'app');
    }

}
