<?php

namespace App\Service\App\Router;

use App\Service\AppConfig;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CaddyRouterHeaderListener
{

    public function __construct(
        private AppConfig $appConfig
    ) {}

    #[AsEventListener]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $router = $_ENV['CADDY_ROUTER'] ?? 'app';
        $event->getResponse()->headers->set(
            'X-Hb-Server',
            (is_string($router) ? $router : 'app') .
            ' / ' . $this->appConfig->getVersion()
        );
    }
}
