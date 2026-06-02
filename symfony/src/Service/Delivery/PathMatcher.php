<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Service\Redirect\RedirectService;

class PathMatcher
{
    public function __construct(
        private readonly RedirectService $redirectService,
    ) {
    }

    public function match(Blog $blog, string $path): DeliveryResponse
    {
        if ($path === '' || $path[0] !== '/') {
            throw new \InvalidArgumentException('Path must start with /');
        }

        $response = $this->matchRedirect($blog, $path);
        if ($response !== null) {
            return $response;
        }

        // TODO: matchDefaultRoutes
        // TODO: setLanguage
        // TODO: matchNonPostRoutes
        // TODO: matchPostRoutes
        // TODO: matchTemplateRoutes

        return DeliveryResponse::forNotFound();
    }

    private function matchRedirect(Blog $blog, string $path): ?DeliveryResponse
    {
        $redirect = $this->redirectService->findRedirectForPath($blog, $path);

        if ($redirect === null) {
            return null;
        }

        return DeliveryResponse::forRedirect($redirect['to'], $redirect['type']);
    }
}
