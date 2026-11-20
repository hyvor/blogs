<?php

namespace App\Service\Delivery;

use App\Service\Blog\BlogService;
use App\Service\Media\BlogIdEncoder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves media from the media URL, independent of the blog's hosting:
 * - {DOMAIN_APP}/media/{blogIdEncoded}/{fileName}/{additional}
 * - {MEDIA_URL}/{blogIdEncoded}/{fileName}/{additional}
 */
class MediaDeliveryService
{

    public function __construct(
        private BlogService $blogService,
        private DeliveryService $deliveryService,
        private BlogHomepageRedirector $homepageRedirector,
    ) {}

    public function getSymfonyResponse(string $blogIdEncoded, string $fileName, ?string $additional): Response
    {
        $blogId = BlogIdEncoder::decode($blogIdEncoded);
        $blog = $blogId !== null ? $this->blogService->getBlogById($blogId) : null;

        if ($blog === null || $this->homepageRedirector->getUnavailableStatus($blog) !== null) {
            return new Response('Not found', 404, ['Content-Type' => 'text/plain']);
        }

        return $this->deliveryService->toSymfonyResponse(
            $this->deliveryService->getMediaResponse($blog, $fileName, $additional)
        );
    }

}
