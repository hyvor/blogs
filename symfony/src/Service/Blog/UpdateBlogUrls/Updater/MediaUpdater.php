<?php

namespace App\Service\Blog\UpdateBlogUrls\Updater;

class MediaUpdater implements UpdaterInterface
{

    public function __construct(
        private readonly string $mediaOldUrl,
        private readonly string $mediaNewUrl
    ) {}

    public function update(string $url): false|string
    {
        if ($url !== $this->mediaOldUrl) {
            return false;
        }

        return $this->mediaNewUrl;
    }
}
