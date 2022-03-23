<?php

namespace App\Data\Objects\DataAPI;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Tag;

class TagObject
{
    public int $id;
    public string $name;
    public string $slug;
    public string $url;
    public ?string $featured_image;
    public $posts_count;

    public function __construct(Tag $tag, Blog $blog)
    {

        $this->id = $tag->id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
        $this->url = PermalinkRepository::getTagPermalink($tag, $blog);
        $this->featured_image = $tag->featured_image;
        $this->posts_count = $tag->posts_count;

    }
}
