<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

use League\Uri\Uri;

class RelativeUrlResolver
{

    // resolves a relative URL or absolute URL to a full URL
    // but, returns null if the scheme is not HTTP or HTTPS
    public static function resolve(string $url, string $baseUrl): ?string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if ($scheme && $scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        return (string)Uri::fromBaseUri($url, $baseUrl);
    }

}