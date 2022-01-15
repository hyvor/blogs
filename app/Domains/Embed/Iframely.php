<?php
namespace App\Domains\Embed;

use App\Domains\Media\Embed\Types\EmbedType;
use Illuminate\Support\Facades\Http;

class Iframely {

    const ENDPOINT = 'https://iframe.ly/api/oembed';

    /**
     * @var string $url - URL to fetch data from
     * 
     * Fetches data from iframely's oembed endpoint
     * https://iframely.com/docs/oembed-api
     */
    static function fetch(string $url) {
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