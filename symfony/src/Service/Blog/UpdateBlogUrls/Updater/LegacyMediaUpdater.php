<?php

namespace App\Service\Blog\UpdateBlogUrls\Updater;

/**
 * Rewrites legacy media URLs that were served from the blog's host
 * ({blogUrl}/media/{name}/...) to the media URL ({mediaBlogBaseUrl}/{name}/...)
 *
 * See PermalinkService::getMediaPermalink
 */
class LegacyMediaUpdater implements UpdaterInterface
{

    private int $updatedCount = 0;

    public function __construct(
        private readonly string $blogUrl,
        private readonly string $mediaBlogBaseUrl,
    ) {}

    public function update(string $url): false|string
    {
        $prefix = $this->blogUrl . '/media/';

        if (!str_starts_with($url, $prefix)) {
            return false;
        }

        $rest = substr($url, strlen($prefix));
        if ($rest === '') {
            return false;
        }

        $this->updatedCount++;
        return $this->mediaBlogBaseUrl . '/' . $rest;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

}
