<?php

namespace App\Data\Objects\ConsoleAPI\Post;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;

class PostVariantObject
{
    public int $language_id;

    public PostStatusEnum $status;
    public string $url;
    public ?string $content;
    public ?string $content_unsaved;
    public ?string $title;
    public ?string $description;

    public function __construct(PostVariant $variant, Post $post, Blog $blog)
    {
        $language = $variant->language;

        $this->status = $variant->status;
        $this->url = PermalinkRepository::getPostPermalink($post, $blog, $language);
        $this->content = $variant->content;
        $this->content_unsaved = $variant->content_unsaved;
        $this->title = $variant->title;
        $this->description = $variant->description;
        $this->language_id = $language->id;
    }
}
