<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\UrlData;

// either a link or embed media
class UrlDataObject
{
    public string $domain;

    public function __construct(
        public string $url, // final URL
        public string $original_url,
        public ?string $title,
        public ?string $description,
        public ?string $thumbnail_url,
        public ?string $icon_url,
        public ?string $site,
    )
    {
        $domain = parse_url($this->url, PHP_URL_HOST);
        $this->domain = is_string($domain) ? $domain : '';
    }

    /**
     * @param array<mixed> $unfolded
     */
    public static function fromUnfolded(array $unfolded) : self
    {

        return new self(
            $unfolded['lastUrl'],
            $unfolded['url'],
            $unfolded['title'],
            $unfolded['description'],
            $unfolded['thumbnailUrl'],
            $unfolded['iconUrl'],
            $unfolded['siteName'],
        );

    }

}
