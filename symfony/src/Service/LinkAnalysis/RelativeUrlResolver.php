<?php

namespace App\Service\LinkAnalysis;

use League\Uri\Uri;

class RelativeUrlResolver
{
    public function resolve(string $url, string $baseUrl): ?string
    {
        $url = trim($url);
        $baseUrl = trim($baseUrl);

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme && $scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        $uri = Uri::parse($url, $baseUrl);
        return $uri ? (string)$uri : null;
    }
}
