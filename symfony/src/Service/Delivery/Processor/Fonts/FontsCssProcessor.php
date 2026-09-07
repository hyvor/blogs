<?php

namespace App\Service\Delivery\Processor\Fonts;

use App\Entity\Blog;
use App\Service\Delivery\Dto\CacheControl;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Integration\Bunny\BunnyService;
use App\Service\Integration\Bunny\UnableToFetchBunnyException;
use App\Service\Route\PermalinkService;

class FontsCssProcessor
{
    public function __construct(
        private BunnyService $bunnyService,
        private PermalinkService $permalinkService,
    ) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $family = $matchedRoute->param('family');
        if ($family === null) {
            return null;
        }

        $blogUrl = $this->permalinkService->getBlogUrl($blog);

        try {
            $css = $this->bunnyService->getCss($blogUrl, $family);
            return DeliveryResponse::forFile(
                DeliveryFileType::ASSET,
                $css,
                'text/css',
                cacheControl: CacheControl::ONE_YEAR,
            );
        } catch (UnableToFetchBunnyException $e) {
            return DeliveryResponse::forError(
                'Failed to fetch font css: ' . $e->getMessage(),
                DeliveryFileType::ASSET,
            );
        }
    }
}
