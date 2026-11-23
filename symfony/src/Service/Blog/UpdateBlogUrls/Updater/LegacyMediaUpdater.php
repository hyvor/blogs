<?php

namespace App\Service\Blog\UpdateBlogUrls\Updater;

/**
 * Rewrites legacy media URLs that were served from a host the blog was hosted at
 * ({legacyBlogUrl}/media/{name}/..., ex: a custom domain or a self-hosting URL)
 * to the media URL on the blog's subdomain ({mediaBaseUrl}/{name}/...)
 *
 * See PermalinkService::getMediaPermalink
 */
class LegacyMediaUpdater implements UpdaterInterface
{

    private int $updatedCount = 0;

    /**
     * @param list<string> $legacyBlogUrls without trailing slashes
     * @param string $mediaBaseUrl {subdomainUrl}/media
     */
    public function __construct(
        private readonly array $legacyBlogUrls,
        private readonly string $mediaBaseUrl,
    ) {}

    public function update(string $url): false|string
    {
        foreach ($this->legacyBlogUrls as $legacyBlogUrl) {
            $prefix = $legacyBlogUrl . '/media/';

            if (!str_starts_with($url, $prefix)) {
                continue;
            }

            $rest = substr($url, strlen($prefix));
            if ($rest === '') {
                return false;
            }

            $this->updatedCount++;
            return $this->mediaBaseUrl . '/' . $rest;
        }

        return false;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

}
