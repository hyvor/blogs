<?php

namespace App\Service\Delivery\Processor\Fonts;

use App\Entity\Blog;
use App\Service\Delivery\BunnyService;
use App\Service\Delivery\CacheControl;
use App\Service\Delivery\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\UnableToFetchBunnyException;
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
            $css = $this->bunnyService->getCss($blog->getId(), $blogUrl, $family);
            return DeliveryResponse::forFile($css, 'text/css', cacheControl: CacheControl::ONE_YEAR);
        } catch (UnableToFetchBunnyException $e) {
            return DeliveryResponse::forError('Failed to fetch font css: ' . $e->getMessage());
        }
    }
}
