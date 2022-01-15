<?php
namespace App\Domains\Media\Embed;

use App\Exceptions\TrustedException;
use App\Models\Embed;

function _safe_length($str, $len = 255) {
    if (!$str) return $str; // null
    return mb_substr($str, 0, $len);
}

class EmbedRepository {

    static function fetch($url) : Embed {

        $embed = Embed::where('url', $url)->first();

        if ($embed) {
            return $embed;
        }

        try {
            $json = Iframely::fetch($url);

            return Embed::create([
                /**
                 * Iframely returns 4 types: link, photo, video, rich 
                 * (https://iframely.com/docs/oembed-api#api-response)
                 * 
                 * We only want either the link or rich
                 */
                'url' => $url,
                'type' => $json['type'] === 'link' ? 'link' : 'rich',
                'html' => $json['html'] ?? null,
                'title' => _safe_length($json['title'] ?? null),
                'description' => _safe_length($json['description'] ?? null),
                'thumbnail' => _safe_length($json['thumbnail_url'] ?? null, 1024)
            ]);

        } catch (IframelyException) {

            // insert to database before sending response so that we don't make multiple requests to
            // iframely endpoint for error URLs

            return Embed::create([
                'url' => $url,
                'type' => 'error'
            ]);

            throw new TrustedException('Unable to fetch');
        }     

    }

}