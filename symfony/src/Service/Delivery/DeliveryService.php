<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Service\Cache\BlogCacheService;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\Dto\DeliveryResponseType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DeliveryService
{
    public function __construct(
        private PathMatcher $pathMatcher,
        /** @phpstan-ignore property.onlyWritten */
        private BlogCacheService $blogCacheService,
        private bool $debug = false,
    ) {}

    public function getResponse(Blog $blog, string $path): DeliveryResponse
    {
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $useCache = $blog->getType() === BlogType::DEFAULT && !$this->debug;

        // TODO: use cache when getCached is implemented
        // if ($useCache) {
        //     $cached = $this->getCached($blog, $path);
        //     if ($cached !== null) {
        //         return $cached;
        //     }
        // }

        $response = $this->pathMatcher->match($blog, $path);

        if ($useCache && $response->cache) {
            $this->setCached($blog, $path, $response);
        }

        return $response;
    }

    public function getSymfonyResponse(Blog $blog, string $path): Response
    {
        $deliveryResponse = $this->getResponse($blog, $path);

        if ($deliveryResponse->type === DeliveryResponseType::REDIRECT) {
            return new RedirectResponse(
                $deliveryResponse->to ?? '/',
                $deliveryResponse->status,
            );
        }

        $response = new Response(
            $deliveryResponse->content,
            $deliveryResponse->status,
        );
        $response->headers->set('Content-Type', $deliveryResponse->mimeType);
        $cacheControlHeader = match ($deliveryResponse->cacheControl) {
            \App\Service\Delivery\Dto\CacheControl::NO_CACHE => 'no-cache, no-store, must-revalidate',
            \App\Service\Delivery\Dto\CacheControl::ONE_WEEK => 'public, max-age=604800',
            \App\Service\Delivery\Dto\CacheControl::ONE_YEAR => 'public, max-age=31536000',
        };
        $response->headers->set('Cache-Control', $cacheControlHeader);
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    /** @phpstan-ignore method.unused */
    private function getCached(Blog $blog, string $path): null
    {
        // BlogCacheService stores timestamps, not responses.
        // TODO: implement response caching
        return null;
    }

    private function setCached(Blog $blog, string $path, DeliveryResponse $response): void
    {
        // TODO: implement response caching
    }
}
