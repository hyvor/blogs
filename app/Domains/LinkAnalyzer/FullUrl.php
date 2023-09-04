<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

class FullUrl
{

    /**
     * This is a very important helper function
     *
     * $url is either an absolute URL or a relative URL
     * $variantUrl is always an absolute URL without any query params.
     *      This is used as the base URL for relative URLs
     */
    public static function getFullUrl(string $url, string $variantUrl) : ?string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if ($scheme === null) {

            if ($url === '')
                return $variantUrl;

            // no scheme, so it's a relative URL
            $variantPath = parse_url($variantUrl, PHP_URL_PATH);
            if (!$variantPath)
                $variantPath = '';
            $baseUrl = substr($variantUrl, 0, strlen($variantUrl) - strlen($variantPath));

            $variantPathSplit = explode('/', $variantPath);
            array_pop($variantPathSplit);
            $variantPathWithoutLast = implode('/', $variantPathSplit);

            $newPath = str_starts_with($url, '/') ?
                $url :
                $variantPathWithoutLast . '/' . $url;

            return $baseUrl . $newPath;
        } else if ($scheme === 'http' || $scheme === 'https') {
            // absolute URL
            return $url;
        }

        return null;
    }

}