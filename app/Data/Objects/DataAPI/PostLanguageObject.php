<?php
namespace App\Data\Objects\DataAPI;

use App\Domains\Post\PostLanguageRepository;
use App\Models\Blog;
use App\Models\Post;

class PostLanguageObject {

    public int $id;
    public string $code;
    public string $name;
    public bool $is_primary;
    public array $variants = [];

    public function __construct(Post $post, Blog $blog) {

        $language = $post->language;

        $this->id = $language->id;
        $this->code = $language->code;
        $this->name = $language->name;
        $this->is_primary =  $language->is_primary;

        $variantPosts = PostLanguageRepository::getVariants($post);

        foreach ($variantPosts as $variantPost) {
            $this->variants[] = new PostLanguageVariantObject($variantPost, $blog);
        }

    }

}