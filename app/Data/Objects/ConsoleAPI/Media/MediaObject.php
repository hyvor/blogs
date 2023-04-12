<?php

namespace App\Data\Objects\ConsoleAPI\Media;

use App\Domains\Route\PermalinkRepository;
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\Media;

class MediaObject
{
    public int $id;

    public ?int $post_id;

    public int $uploaded_at;

    public string $url;

    public string $name;

    public string $original_name;

    public ?string $extension;

    public function __construct(Media $media, Blog $blog)
    {

        $this->id = $media->id;
        $this->post_id = $media->post_id;
        $this->uploaded_at = $media->created_at->getTimestamp();
        $this->name = $media->name ?? '';
        $this->url = PermalinkRepository::getMediaPermalink($media, $blog);
        $this->original_name = $media->original_name;
        $this->extension = $media->extension;

    }
}
