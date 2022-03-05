<?php
namespace App\Data\Objects\ConsoleAPI\Media;

use App\Domains\Route\PermalinkRepository;
use App\Models\Media;

class MediaObject
{
    public int $id;
    public int $uploaded_at;
    public int $blog_id;
    public string $url;
    public string $name;
    public string $extension;

    public function __construct(Media $media)
    {

        $this->id = $media->id;
        $this->uploaded_at = $media->created_at->timestamp;
        $this->blog_id = $media->blog_id;
        $this->name = $media->name;
        $this->url = PermalinkRepository::getMediaPermalink($media, $media->blog);
        $this->original_name = $media->original_name;
        $this->extension = $media->extension;

    }

}
