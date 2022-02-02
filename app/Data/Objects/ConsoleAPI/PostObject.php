<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Objects\DataAPI\TagObject;
use App\Models\Blog;
use App\Models\Post;

class PostObject
{
    public $id;
    public $created_at;
    public $updated_at;
    public $published_at;
    public $status;
    public $is_featured;
    public $is_page;
    public $slug;
    public $content;
    public $title;
    public $description;
    public $url;
    public $featured_image;
    public $canonical_url;
    public $reading_time;
    public $code_head;
    public $code_foot;


    public function __construct(Post $post, Blog $blog)
    {

        $tags = $post->tags->map(function ($tag) use ($blog) {
            return new TagObject($tag, $blog);
        })->toArray();

        $authors = null;

        $this->id = $post->id;
        $this->created_at = $post->created_at->timestamp;
        $this->updated_at = $post->updated_at->timestamp;
        $this->published_at = $post->published_at?->timestamp;
        $this->status = $post->status;
        $this->is_featured = $post->is_featured;
        $this->is_page = $post->is_page;
        $this->slug = $post->slug;
        $this->content = $post->content;
        $this->title = $post->title;
        $this->description = $post->description;
        $this->url = $post->url;
        $this->featured_image = $post->featured_image;
        $this->canonical_url = $post->canonical_url;
        $this->reading_time = $post->reading_time;
        $this->code_head = $post->code_head;
        $this->code_foot = $post->code_foot;

        $this->tags = $tags;
        $this->authors = $authors;
    }
}
