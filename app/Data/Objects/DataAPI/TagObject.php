<?php

namespace App\Data\Objects\DataAPI;

use App\Domains\Blog\BlogRepository;
use App\Models\Blog;
use App\Models\Tag;

class TagObject
{
    public $id;
    public $name;
    public $slug;
    public $url;
    public $featured_image;
    public $posts_count;

    public function __construct(Tag $tag, Blog $blog)
    {

        $this->id = $tag->id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
        $this->url = BlogRepository::getFullUrlFromPath($blog, 'tag/' . $tag->slug);
        $this->featured_image = $tag->featured_image;
        $this->posts_count = $tag->posts_count;
    }
}
