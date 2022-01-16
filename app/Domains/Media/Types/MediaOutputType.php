<?php
namespace App\Domains\Media\Types;

use App\Models\Media;

class MediaOutputType {

    public int $id;
    public int $uploaded_at;
    public int $blog_id;
    public string $url;
    public string $name;
    public string $extension;

    public function __construct(Media $media) {

        $this->id = $media->id;
        $this->uploaded_at = $media->created_at->timestamp;
        $this->blog_id = $media->blog_id;
        $this->url = $media->url;
        $this->name = $media->name;
        $this->extension = $media->extension;

    }

}