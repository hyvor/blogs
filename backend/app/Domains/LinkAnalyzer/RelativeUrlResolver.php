<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

use League\Uri\Exceptions\SyntaxError;
use League\Uri\Uri;

class RelativeUrlResolver
{

    // resolves a relative URL or absolute URL to a full URL
    // but, returns null if the scheme is not HTTP or HTTPS or if the URL is invalid
    public static function resolve(string $url, string $baseUrl): ?string
    {
        $url = trim($url);
        $baseUrl = trim($baseUrl);

        $scheme = parse_url($url, PHP_URL_SCHEME);
        // if the URL is already a full URL, but not HTTP or HTTPS, return null
        if ($scheme && $scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        try {
            return (string)Uri::fromBaseUri($url, $baseUrl);
        } catch (SyntaxError) {
            return null;
        }
    }

}