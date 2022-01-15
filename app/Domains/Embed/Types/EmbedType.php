<?php
namespace App\Domains\Embed\Types;

use App\Models\Embed;

// either a link or rich media
class EmbedType {

    public $type;
    public $html;
    public $url;
    public $title;
    public $description;
    public $thumbnail;

    public function __construct(Embed $embed) {
        $this->type = $embed->type;
        $this->html = $embed->html;
        $this->url = $embed->url;
        $this->title = $embed->title;
        $this->description = $embed->description;
        $this->thumbnail = $embed->thumbnail;
    }

}

