<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\UrlDataTypeEnum;
use App\Models\UrlData;

// either a link or rich media
class UrlDataObject
{

    public string $url; // final URL
    public string $domain;
    public UrlDataTypeEnum $type;
    public ?string $html;
    public string $title;
    public string $description;
    public ?string $thumbnail;
    public ?string $icon;
    public ?string $site;

    public function __construct(UrlData $urlData)
    {

        $this->url = $urlData->final_url;
        $this->domain = parse_url($this->url, PHP_URL_HOST);
        $this->type = UrlDataTypeEnum::from($urlData->type);
        $this->html = $urlData->html;
        $this->title = $urlData->title ?? '';
        $this->description = $urlData->description ?? '';
        $this->thumbnail = $urlData->thumbnail;
        $this->icon = $urlData->icon;
        $this->site = $urlData->site;

    }
}
