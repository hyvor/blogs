<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Service\Cache\BlogCacheService;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\Dto\DeliveryResponseType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DeliveryService
{
    public function __construct(
        private PathMatcher $pathMatcher,
        private BlogCacheService $blogCacheService,
        #[Autowire('%kernel.debug%')]
        private bool $debug = false,
    ) {}

    public function getResponse(Blog $blog, string $path): DeliveryResponse
    {
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $useCache = $blog->getType() === BlogType::DEFAULT && !$this->debug;

         if ($useCache) {
             $cached = $this->blogCacheService->getResponse($blog, $path);
             if ($cached !== null) {
                 return $cached;
             }
         }

        $response = $this->pathMatcher->match($blog, $path);

        if ($useCache && $response->cache) {
            $this->blogCacheService->setResponse($blog, $path, $response);
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
        $response->headers->set('Cache-Control', $deliveryResponse->cacheControl->toHeaderValue());
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
