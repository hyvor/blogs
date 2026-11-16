<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Service\Cache\BlogCacheService;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\Processor\MediaProcessor;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DeliveryService
{
    public function __construct(
        private PathMatcher $pathMatcher,
        private MediaProcessor $mediaProcessor,
        private BlogCacheService $blogCacheService,
        #[Autowire('%kernel.debug%')]
        private bool $debug = false,
    ) {}

    public function getResponse(Blog $blog, string $path): DeliveryResponse
    {
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return $this->cached(
            $blog,
            $path,
            fn() => $this->pathMatcher->match($blog, $path)
        );
    }

    /**
     * Media served from the media URL (see PermalinkService::getMediaPermalink)
     * The cache is shared with the legacy {blogUrl}/media/{file_name} route.
     */
    public function getMediaResponse(Blog $blog, string $fileName, ?string $additional = null): DeliveryResponse
    {
        return $this->cached(
            $blog,
            MediaProcessor::getCachePath($fileName, $additional),
            fn() => $this->mediaProcessor->processFile($blog, $fileName, $additional) ?? DeliveryResponse::forNotFound(),
            // only media files are cached here: the legacy route may have cached something else
            // for this path (ex: a redirect or a 404 page), and 404s are not worth caching
            fn(DeliveryResponse $response) => $response->fileType === DeliveryFileType::MEDIA,
        );
    }

    /**
     * @param callable(): DeliveryResponse $resolve
     * @param (callable(DeliveryResponse): bool)|null $isCacheable additional check for reading/writing the cache
     */
    private function cached(
        Blog $blog,
        string $path,
        callable $resolve,
        ?callable $isCacheable = null,
    ): DeliveryResponse
    {
        $useCache = $blog->getType() === BlogType::DEFAULT && !$this->debug;

        if ($useCache) {
            $cached = $this->blogCacheService->getResponse($blog, $path);
            if ($cached !== null && ($isCacheable === null || $isCacheable($cached))) {
                $cached->setFromCache(true);
                return $cached;
            }
        }

        $response = $resolve();

        if ($useCache && $response->cache && ($isCacheable === null || $isCacheable($response))) {
            $this->blogCacheService->setResponse($blog, $path, $response);
        }

        return $response;
    }

    public function getSymfonyResponse(Blog $blog, string $path): Response
    {
        return $this->toSymfonyResponse($this->getResponse($blog, $path));
    }

    public function toSymfonyResponse(DeliveryResponse $deliveryResponse): Response
    {
        if ($deliveryResponse->type === DeliveryResponseType::REDIRECT) {
            $response = new RedirectResponse(
                $deliveryResponse->to ?? '/',
                $deliveryResponse->status,
            );
        } else {
            $response = new Response(
                $deliveryResponse->content,
                $deliveryResponse->status,
            );
            $response->headers->set('Content-Type', $deliveryResponse->mimeType);
            $response->headers->set('Cache-Control', $deliveryResponse->cacheControl->toHeaderValue());
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        $response->headers->set('X-HB-Cache', $deliveryResponse->isFromCache() ? 'HIT' : 'MISS');

        return $response;
    }
}
