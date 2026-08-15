<?php

namespace App\Service\App\Router;

use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsRoutingConditionService('app_router')]
class AppRouter
{

    public function __construct(
        private RequestStack $requestStack
    ) {}

    /**
     * @return 'app'|'subdomain'|'customdomain'|'local'
     */
    private function route(): string
    {
        if ($this->isSubrequest()) {
            // subrequests always route to app
            // for example, when template rendering calls Data API
            return 'app';
        }

        return $_ENV['CADDY_ROUTER'] ?? 'app';
    }

    private function isSubrequest(): bool
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        $mainRequest = $this->requestStack->getMainRequest();
        // If they are not the exact same object, it's a subrequest
        return $currentRequest !== $mainRequest;
    }

    public function isApp(): bool
    {
        return $this->route() === 'app';
    }

    public function isSubdomain(): bool
    {
        return $this->route() === 'subdomain';
    }

    public function isCustomDomain(): bool
    {
        return $this->route() === 'customdomain';
    }

    public function isLocal(): bool
    {
        return $this->route() === 'local';
    }

}
