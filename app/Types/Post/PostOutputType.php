<?php
namespace App\Types\Post;

use App\Models\Blog;
use App\Models\Post;
use App\Types\Tag\TagType;

class PostOutputType {

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


    /**
     * @var $isConsole - If this type is for Console API.
     */
    public function __construct(Post $post, Blog $blog, bool $isConsole = false) {

        $tags = $post->tags->map(function($tag) use ($blog, $isConsole) {
            return $isConsole ? $tag->id : new TagType($tag, $blog);
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

