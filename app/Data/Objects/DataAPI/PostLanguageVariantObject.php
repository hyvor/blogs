<?php
namespace App\Data\Objects\DataAPI;

use App\Domains\Post\PostLanguageRepository;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;

class PostLanguageVariantObject {

    public int $id;
    public string $code;
    public string $name;
    public bool $is_primary;
    public string $post_url;

    public function __construct(Post $post, Blog $blog) {

        $language = $post->language;

        $this->id = $language->id;
        $this->code = $language->code;
        $this->name = $language->name;
        $this->is_primary =  $language->is_primary;
        $this->post_url = PermalinkRepository::getPostPermalink($post, $blog);

    }

}