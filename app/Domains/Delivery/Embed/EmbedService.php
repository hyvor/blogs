<?php

namespace App\Domains\Delivery\Embed;

use App\Models\Blog;

class EmbedService
{
    public static function validateEmbeddingUrl(Blog $blog, string $embeddingUrl): bool
    {
        $allowedDomains = $blog->getMeta('embedding_domains');

        if ($allowedDomains === '*') {
            return true;
        }

        $domains = array_map(function (string $domain) {
            $domain = trim($domain);
            // in case the user accidentally adds https://
            $domain = preg_replace('/^https?:\/\//', '', $domain);
            // remove path
            $domain = explode('/', $domain)[0];
            // remove port
            $domain = preg_replace('/:\d+/', '', $domain);
            return $domain;
        }, explode(',', $allowedDomains));
        $embeddingDomain = parse_url($embeddingUrl, PHP_URL_HOST);

        return in_array($embeddingDomain, $domains);
    }
}
