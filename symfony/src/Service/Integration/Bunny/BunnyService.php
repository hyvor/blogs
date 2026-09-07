<?php

namespace App\Service\Integration\Bunny;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BunnyService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
    ) {}

    /** @throws UnableToFetchBunnyException */
    public function getCss(string $blogUrl, string $fontFamily): string
    {
        $url = "https://fonts.bunny.net/css?family=$fontFamily&display=swap";
        $cacheKey = 'bunny_fonts_' . md5($url);

        $css = $this->cache->get($cacheKey, function (ItemInterface $item) use ($url) {
            $item->expiresAfter(30 * 24 * 60 * 60);

            try {
                $response = $this->httpClient->request('GET', $url);
                $statusCode = $response->getStatusCode();
            } catch (ExceptionInterface $e) {
                throw new UnableToFetchBunnyException('Connection failed');
            }

            if ($statusCode >= 400) {
                throw new UnableToFetchBunnyException('Request failed');
            }

            return $response->getContent();
        });

        return str_replace(
            'https://fonts.bunny.net/',
            $blogUrl . '/fonts/file/',
            $css
        );
    }
}
