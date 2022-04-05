<?php

namespace App\Data\Objects\ConsoleAPI\Post;

use App\Data\Objects\ConsoleAPI\TagObject;
use App\Data\Objects\ConsoleAPI\AuthorObject;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;

class PostObject
{

    public int $id;
    public string $preview_id; // an encrypted ID for preview
    public int $created_at;
    public int $updated_at;
    public bool $is_featured;
    public bool $is_page;
    public string $slug;
    public ?string $canonical_url;
    public ?int $reading_time;
    public ?string $code_head;
    public ?string $code_foot;

    public function __construct(Post $post, Blog $blog)
    {

        $tags = $post->tags->map(function ($tag) use ($blog) {
            return new TagObject($tag, $blog);
        })->toArray();

        // $authors = $post->authors->map(function ($author) use ($blog) {
        //     return new AuthorObject($author, $blog);
        // })->toArray();
        $authors = null;

        $this->id = $post->id;
        $this->preview_id = encrypt($post->id);
        $this->created_at = $post->created_at->timestamp;
        $this->updated_at = $post->updated_at->timestamp;
        $this->slug = $post->slug;
        $this->is_page = (bool) $post->is_page;
        $this->is_featured = (bool) $post->is_featured;
        $this->canonical_url = $post->canonical_url;
        $this->reading_time = $post->reading_time;
        $this->code_head = $post->code_head;
        $this->code_foot = $post->code_foot;

        // variants
        $this->variants = $post->variants->map(function($variant) use ($blog, $post) {
            return new PostVariantObject($variant, $post, $blog);
        })->keyBy('language_id');

        // tags
        $this->tags = $post->tags->map(function ($tag) use ($blog) {
            return new TagObject($tag, $blog);
        })->toArray();

        $this->authors = $post->authors->map(function ($author) use ($blog) {
            return new AuthorObject($author, $blog);
        });
    }
}
