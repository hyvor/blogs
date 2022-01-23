<?php

namespace App\Domains\Media\Embed\Types;

// either a link or rich media
class EmbedType
{
    public $type;
    public $html;
    public $url;
    public $title;
    public $description;
    public $thumbnail;

    public function __construct($type, $html, $url, $title, $description, $thumbnail)
    {

        $this->type = $type;
        $this->html = $html;
        $this->url = $url;
        $this->title = $title;
        $this->description = $description;
        $this->thumbnail = $thumbnail;
    }
}
