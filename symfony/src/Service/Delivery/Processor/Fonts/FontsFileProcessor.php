<?php

namespace App\Service\Delivery\Processor\Fonts;

use App\Entity\Blog;
use App\Service\Delivery\CacheControl;
use App\Service\Delivery\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FontsFileProcessor
{
    public function __construct(private HttpClientInterface $httpClient) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $path = $matchedRoute->param('path');
        if ($path === null) {
            return null;
        }

        $url = "https://fonts.bunny.net/$path";

        try {
            $response = $this->httpClient->request('GET', $url);
            $statusCode = $response->getStatusCode();
        } catch (\Exception) {
            return DeliveryResponse::forError('Failed to fetch font file: Connection failed');
        }

        if ($statusCode >= 400) {
            return DeliveryResponse::forError('Failed to fetch font file: Request failed');
        }

        $contentType = $response->getHeaders()['content-type'][0] ?? 'application/octet-stream';

        return DeliveryResponse::forFile(
            $response->getContent(),
            $contentType,
            cacheControl: CacheControl::ONE_YEAR,
        );
    }
}
