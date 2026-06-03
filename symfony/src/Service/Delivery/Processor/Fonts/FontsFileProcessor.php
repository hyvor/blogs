<?php

namespace App\Service\Delivery\Processor\Fonts;

use App\Entity\Blog;
use App\Service\Delivery\Dto\CacheControl;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FontsFileProcessor
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $path = $matchedRoute->param('path');
        if ($path === null) {
            return null;
        }

        $url = "https://fonts.bunny.net/$path";

        try {
            $response = $this->httpClient->request('GET', $url);
            $content = $response->getContent();
            $contentType = $response->getHeaders()['content-type'][0] ?? 'application/octet-stream';

        } catch (ExceptionInterface $e) {
            return DeliveryResponse::forError(
                DeliveryFileType::ASSET,
                'Failed to fetch font file: ' . ($e instanceof TransportExceptionInterface ? 'Connection failed' : 'Request failed'),
            );
        }

        return DeliveryResponse::forFile(
            DeliveryFileType::ASSET,
            $content,
            $contentType,
            cacheControl: CacheControl::ONE_YEAR,
        );
    }
}
