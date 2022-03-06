<?php

namespace App\Domains\UrlData;

use Illuminate\Support\Facades\Http;

class Iframely
{
    private const ENDPOINT = 'https://iframe.ly/api/oembed';

    /**
     * @var string $url - URL to fetch data from
     *
     * Fetches data from iframely's oembed endpoint
     * https://iframely.com/docs/oembed-api
     */
    public static function fetch(string $url)
    {
        $params = http_build_query([
            'url' => $url,
            'api_key' => config('services.iframely.key')
        ]);

        $requestUrl = self::ENDPOINT . '?' . $params;
        $response = Http::get($requestUrl);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new IframelyException('Unable to fetch');
        }
    }
}
