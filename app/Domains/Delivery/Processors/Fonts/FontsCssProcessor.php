<?php declare(strict_types=1);

namespace App\Domains\Delivery\Processors\Fonts;

use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\Processors\RouteProcessorAbstract;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use Illuminate\Support\Facades\Http;

class FontsCssProcessor extends RouteProcessorAbstract
{

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {

        $blogUrl = $pathMatcher->blog->url();

        $family = $matchedRoute->param('family');

        $url = "https://fonts.bunny.net/css?family=$family&display=swap";
        $response = Http::get($url);
        $response->throw();

        $css = $response->body();
        $css = str_replace(
            'https://fonts.bunny.net/',
            $blogUrl . '/fonts/file/',
            $css
        );

        $this->setResponseObject(DeliveryAPIResponseObject::forFile(
            DeliveryAPIFileTypeEnum::ASSET,
            $css,
            'text/css',
            true,
            200,
            DeliveryAPICacheControlHeaderEnum::CACHE_ONE_YEAR
        ));

    }
}