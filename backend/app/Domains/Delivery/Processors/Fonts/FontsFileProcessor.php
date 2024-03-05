<?php declare(strict_types=1);

namespace App\Domains\Delivery\Processors\Fonts;

use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\Processors\RouteProcessorAbstract;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class FontsFileProcessor extends RouteProcessorAbstract
{

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {

        $path = $matchedRoute->param('path');

        $url = "https://fonts.bunny.net/$path";
        try {
            $response = Http::get($url);
            $response->throw();
        } catch (ConnectionException|RequestException $e) {
            $errorMessage = $e instanceof ConnectionException ?
                'Connection failed' :
                'Request failed';
            $this->setResponseObject(DeliveryAPIResponseObject::forError(
                DeliveryAPIFileTypeEnum::ASSET,
                'Failed to fetch font file: ' . $errorMessage,
            ));
            return;
        }

        $this->setResponseObject(DeliveryAPIResponseObject::forFile(
            DeliveryAPIFileTypeEnum::ASSET,
            $response->body(),
            $response->header('Content-Type'),
            true,
            200,
            DeliveryAPICacheControlHeaderEnum::CACHE_ONE_YEAR
        ));

    }
}