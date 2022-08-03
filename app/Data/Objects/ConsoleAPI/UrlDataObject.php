<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\UrlData;

// either a link or embed media
class UrlDataObject
{
    public string $url; // final URL

    public string $domain;

    public ?string $html;

    public string $title;

    public string $description;

    public ?string $thumbnail_url;

    public ?string $icon_url;

    public ?string $site;

    public function __construct(UrlData $urlData)
    {
        $this->url = $urlData->final_url;
        $this->domain = parse_url($this->url, PHP_URL_HOST);
        $this->html = $urlData->html;
        $this->title = $urlData->title ?? '';
        $this->description = $urlData->description ?? '';
        $this->thumbnail_url = $urlData->thumbnail_url;
        $this->icon_url = $urlData->icon_url;
        $this->site = $urlData->site;
    }
}
