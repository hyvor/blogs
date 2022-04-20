<?php

namespace App\Domains\UrlData;

use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Exceptions\TrustedException;
use App\Models\UrlData;

function _safe_length($str, $len = 255)
{
    if (!$str) {
        return $str; // null
    }
    return mb_substr($str, 0, $len);
}

class UrlDataRepository
{
    public static function fetch($url, UrlDataFetchTypeEnum $fetchType): UrlData
    {

        $embed = UrlData::where('url', $url)
            ->where('fetch_type', $fetchType)
            ->first();

        if ($embed) {
            return $embed;
        }

        try {
            $json = Iframely::fetch($url);

            return UrlData::create([
                'fetch_type' => $fetchType,
                /**
                 * Iframely returns 4 types: link, photo, video, rich
                 * (https://iframely.com/docs/oembed-api#api-response)
                 *
                 * We only want either the link or rich
                 */
                'url' => $url,
                'final_url' => $json['url'],
                'type' => isset($json['html']) ? 'rich' : 'link',
                'html' => $json['html'] ?? null,
                'title' => _safe_length($json['meta']['title'] ?? null),
                'description' => _safe_length($json['meta']['description'] ?? null),
                'thumbnail' => _safe_length($json['links']['thumbnail'][0]['href'] ?? null),
                'icon' => _safe_length($json['links']['icon'][0]['href'] ?? null),
                'site' => _safe_length($json['meta']['site'] ?? null)
            ]);

        } catch (IframelyException) {
            // insert to database before sending response so that we don't make multiple requests to
            // iframely endpoint for error URLs

            return UrlData::create([
                'url' => $url,
                'type' => 'error'
            ]);

            throw new TrustedException('Unable to fetch');
        }
    }
}
