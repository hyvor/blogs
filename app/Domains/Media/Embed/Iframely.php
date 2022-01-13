<?php
namespace App\Domains\Media\Embed;

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
    static function fetch(string $url) : EmbedType {
        $params = http_build_query([
            'url' => $url,
            'api_key' => config('services.iframely.key'),
            'iframe' => 0, // disable IFRAMELY iframe (https://iframely.com/docs/parameters#iframe=0)
            /**
             * This is disable to make sure normal URLs are not handled by iframely (cards)
             */
        ]);

        $requestUrl = self::ENDPOINT . '?' . $params;
        $response = Http::get($requestUrl);

        if ($response->successful()) {
            $json = $response->json();

            return new EmbedType(
                /**
                 * Iframely returns 4 types: link, photo, video, rich 
                 * (https://iframely.com/docs/oembed-api#api-response)
                 * 
                 * We only want either the link or rich
                 */
                $json['type'] === 'link' ? 'link' : 'rich',
                $json['html'] ?? '',
                $json['url'],
                $json['title'],
                $json['description'],
                $json['thumbnail_url']
            );

        } else {
            throw new IframelyException('Unable to fetch');
        }

    }

}