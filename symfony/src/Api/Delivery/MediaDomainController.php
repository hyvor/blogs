<?php

namespace App\Api\Delivery;

use App\Service\Delivery\MediaDeliveryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Serves media on MEDIA_URL (CADDY_ROUTER=media)
 * See AppDeliveryController::media for the default DOMAIN_APP/media/... URLs
 */
class MediaDomainController
{

    public function __construct(
        private MediaDeliveryService $mediaDeliveryService,
    ) {}

    #[Route(
        '/{blogIdEncoded}/{fileName}/{additional}',
        name: 'media_domain_delivery',
        defaults: ['additional' => null],
        methods: 'GET',
    )]
    public function serve(string $blogIdEncoded, string $fileName, ?string $additional): Response
    {
        return $this->mediaDeliveryService->getSymfonyResponse($blogIdEncoded, $fileName, $additional);
    }

}
