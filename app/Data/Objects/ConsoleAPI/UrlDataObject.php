<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\UrlData;

// either a link or rich media
class UrlDataObject
{
    public $type;
    public $html;
    public $url;
    public $title;
    public $description;
    public $thumbnail;

    public function __construct(UrlData $urlData)
    {
        $this->type = $urlData->type;
        $this->html = $urlData->html;
        $this->url = $urlData->url;
        $this->title = $urlData->title;
        $this->description = $urlData->description;
        $this->thumbnail = $urlData->thumbnail;
    }
}
