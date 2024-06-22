<?php

namespace App\Domains\UrlData;

use App\Data\Enums\ResultEnum;
use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Exceptions\TrustedException;
use App\Models\UrlData;

function _safe_length(string $str, int $len = 255) : string
{
    if (! $str) {
        return $str; // null
    }

    return mb_substr($str, 0, $len);
}

class UrlDataRepository
{
    public static function fetch(string $url, UrlDataFetchTypeEnum $fetchType): UrlData
    {
        $embed = UrlData::where('url', $url)
            ->where('fetch_type', $fetchType)
            ->first();

        if ($embed) {

            if ($fetchType === UrlDataFetchTypeEnum::EMBED && empty($embed->html)) {
                throw new IframelyException('HTML is empty for embed');
            }

            return $embed;
        }

        try {
            $json = Iframely::fetch($url);

            if ($fetchType === UrlDataFetchTypeEnum::EMBED && empty($json['html'])) {
                throw new IframelyException('HTML is empty for embed');
            }

            return UrlData::create([
                'result' => ResultEnum::OK,
                'fetch_type' => $fetchType,
                /**
                 * Iframely returns 4 types: link, photo, video, rich
                 * (https://iframely.com/docs/oembed-api#api-response)
                 *
                 * We only want either the link or rich
                 */
                'url' => $url,
                'final_url' => $json['url'] ?? $url,
                'html' => $json['html'] ?? null,
                'title' => _safe_length($json['meta']['title'] ?? null),
                'description' => _safe_length($json['meta']['description'] ?? null),
                'thumbnail_url' => _safe_length($json['links']['thumbnail'][0]['href'] ?? null),
                'icon_url' => _safe_length($json['links']['icon'][0]['href'] ?? null),
                'site' => _safe_length($json['meta']['site'] ?? null),
            ]);
        } catch (IframelyException) {

            // insert to database before sending response so that we don't make multiple requests to
            // iframely endpoint for error URLs
            UrlData::create([
                'result' => ResultEnum::ERR,
                'fetch_type' => $fetchType,
                'url' => $url,
                'final_url' => $url
            ]);

            throw new TrustedException('Unable to fetch data');
        }
    }
}
