<?php

namespace App\Service\Blog\UpdateBlogUrls\Updater;

/**
 * Rewrites Hyvor's old Cloudinary URLs to media.hyvor.com
 *
 * https://res.cloudinary.com/dqabfne6s/video/upload/v1726698292/blogs.hyvor.com/dream-big.mp3
 * -> https://media.hyvor.com/blogs.hyvor.com/dream-big.mp3
 */
class CloudinaryUpdater implements UpdaterInterface
{

    private const string PATTERN = '#^https://res\.cloudinary\.com/dqabfne6s/(?:image|video|raw)/upload/(?:v\d+/)?(.+)$#';
    private const string NEW_BASE = 'https://media.hyvor.com/';

    /**
     * @var array<string, string> old URL => new URL, for debugging
     */
    private array $matched = [];

    public function update(string $url): false|string
    {
        if (preg_match(self::PATTERN, $url, $matches) !== 1) {
            return false;
        }

        $newUrl = self::NEW_BASE . $matches[1];
        $this->matched[$url] = $newUrl;
        return $newUrl;
    }

    public function getUpdatedCount(): int
    {
        return count($this->matched);
    }

    /**
     * @return array<string, string> all matched URLs (old => new)
     */
    public function getMatched(): array
    {
        return $this->matched;
    }

}
