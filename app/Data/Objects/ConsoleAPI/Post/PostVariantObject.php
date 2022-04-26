<?php
namespace App\Data\Objects\ConsoleAPI\Post;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;

class PostVariantObject {

    public string $status;
    public ?int $published_at;
    public bool $is_featured;
    public bool $is_page;
    public string $slug;
    public ?string $content;
    public ?string $content_unsaved;
    public ?string $title;
    public ?string $description;
    public string $url;
    public ?string $featured_image;

    public function __construct(PostVariant $variant, Post $post, Blog $blog) {

        $language = $variant->language;

        $this->status = $variant->status;
        $this->published_at = $post->published_at?->timestamp;
        $this->content = $variant->content;
        $this->content_unsaved = $variant->content_unsaved;
        $this->title = $variant->title;
        $this->description = $variant->description;
        $this->url = PermalinkRepository::getPostPermalink($post, $blog, $language);
        $this->featured_image = $variant->featured_image;
        $this->language_id = $language->id;

    }

}