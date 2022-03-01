<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Objects\DataAPI\TagObject;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Post;

class PostObject
{
    public int $id;
    public string $preview_id; // an encrypted ID for preview
    public int $created_at;
    public int $updated_at;
    public ?int $published_at;
    public string $status;
    public bool $is_featured;
    public bool $is_page;
    public string $slug;
    public ?string $content;
    public ?string $content_unsaved;
    public ?string $title;
    public ?string $description;
    public string $url;
    public ?string $featured_image;
    public ?string $canonical_url;
    public ?int $reading_time;
    public ?string $code_head;
    public ?string $code_foot;


    public function __construct(Post $post, Blog $blog)
    {

        $tags = $post->tags->map(function ($tag) use ($blog) {
            return new TagObject($tag, $blog);
        })->toArray();

        $authors = null;

        $this->id = $post->id;
        $this->preview_id = encrypt($post->id);
        $this->created_at = $post->created_at->timestamp;
        $this->updated_at = $post->updated_at->timestamp;
        $this->published_at = $post->published_at?->timestamp;
        $this->status = $post->status;
        $this->is_featured = (bool) $post->is_featured;
        $this->is_page = (bool) $post->is_page;
        $this->slug = $post->slug;
        $this->content = $post->content;
        $this->content_unsaved = $post->content_unsaved;
        $this->title = $post->title;
        $this->description = $post->description;
        $this->url = PermalinkRepository::getPostPermalink($post, $blog);
        $this->featured_image = $post->featured_image;
        $this->canonical_url = $post->canonical_url;
        $this->reading_time = $post->reading_time;
        $this->code_head = $post->code_head;
        $this->code_foot = $post->code_foot;

        $this->tags = $tags;
        $this->authors = $authors;
    }
}
