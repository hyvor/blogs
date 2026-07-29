<?php

namespace App\Service\Blog\UpdateBlogUrls\Updater;

class HostingUpdater implements UpdaterInterface
{

    public function __construct(
        private readonly string $blogOldUrl,
        private readonly string $blogNewUrl
    ) {}

    public function update(string $url): false|string
    {
        if (!str_starts_with($url, $this->blogOldUrl)) {
            return false;
        }

        $path = substr($url, strlen($this->blogOldUrl));
        return $this->blogNewUrl . $path;
    }

}
