<?php

namespace App\Domains\Integrations\Bunny;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BunnyService
{

    /**
     * @throws UnableToFetchBunnyException
     */
    public static function getCss(int $blogId, string $blogUrl, string $fontFamily): string
    {
        $url = "https://fonts.bunny.net/css?family=$fontFamily&display=swap";
        $cacheKey = "bunny-fonts-$blogId-$url";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::get($url);
            $response->throw();
        } catch (ConnectionException|RequestException $e) {
            throw new UnableToFetchBunnyException(
                $e instanceof ConnectionException ?
                    'Connection failed' :
                    'Request failed'
            );
        }

        $css = $response->body();
        $css = str_replace(
            'https://fonts.bunny.net/',
            $blogUrl . '/fonts/file/',
            $css
        );

        // cache for 30 days
        Cache::put($cacheKey, $css, 30 * 24 * 60 * 60);

        return $css;
    }

}
