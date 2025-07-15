<?php

declare(strict_types=1);

namespace App\Domains\Delivery\Processors\Fonts;

use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\Processors\RouteProcessorAbstract;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Integrations\Bunny\BunnyService;
use App\Domains\Integrations\Bunny\UnableToFetchBunnyException;

class FontsCssProcessor extends RouteProcessorAbstract
{

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {
        $blogUrl = $pathMatcher->blog->url();
        $family = $matchedRoute->param('family');

        try {
            $css = BunnyService::getCss($blogUrl, (string)$family);

            $this->setResponseObject(
                DeliveryAPIResponseObject::forFile(
                    DeliveryAPIFileTypeEnum::ASSET,
                    $css,
                    'text/css',
                    true,
                    200,
                    DeliveryAPICacheControlHeaderEnum::CACHE_ONE_YEAR
                )
            );
        } catch (UnableToFetchBunnyException $e) {
            $this->setResponseObject(
                DeliveryAPIResponseObject::forError(
                    DeliveryAPIFileTypeEnum::ASSET,
                    'Failed to fetch font css: ' . $e->getMessage(),
                )
            );
        }
    }
}