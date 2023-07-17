<?php

namespace App\Data\Objects\ConsoleAPI\Post;

use App\Data\Objects\ConsoleAPI\Tag\TagObject;
use App\Data\Objects\ConsoleAPI\User\UserObject;
use App\Domains\Delivery\PostPreviewSecretEncryptor;
use App\Models\Blog;
use App\Models\Post;

class PostObject
{
    public int $id;

    public string $preview_id;

    public int $created_at;

    public int $updated_at;

    public ?int $published_at;

    public bool $is_featured;

    public bool $is_page;

    // public ?string $slug;

    public ?string $featured_image_url;

    public ?string $canonical_url;

    public ?string $code_head;

    public ?string $code_foot;

    public ?UserObject $editing_user;

    /**
     * @var PostVariantObject[]
     */
    public array $variants;

    /**
     * @var TagObject[]
     */
    public array $tags;

    /**
     * @var UserObject[]
     */
    public array $authors;

    public function __construct(
        Post $post,
        Blog $blog,
        bool $setHtml = false
    )
    {
        $this->id = $post->id;
        $this->preview_id = PostPreviewSecretEncryptor::getPreviewSecret($post);
        $this->created_at = $post->created_at->getTimestamp();
        $this->updated_at = $post->updated_at->getTimestamp();
        $this->published_at = $post->published_at?->getTimestamp();
        $this->is_page = (bool) $post->is_page;
        $this->is_featured = (bool) $post->is_featured;
        $this->featured_image_url = $post->featured_image_url;
        $this->canonical_url = $post->canonical_url;
        $this->code_head = $post->code_head;
        $this->code_foot = $post->code_foot;
        $this->editing_user = $post->editing_user? new UserObject($post->editing_user, $blog) : null;

        /** @var PostVariantObject[] $variants */
        $variants = $post->variants->map(function ($variant) use ($blog, $post, $setHtml) {
            return new PostVariantObject($variant, $post, $blog, $setHtml);
        })->sortBy('language_id')->toArray();


        /** @var TagObject[] $tags */
        $tags = $post->tags->map(function ($tag) use ($blog) {
            return new TagObject($tag, $blog);
        })->toArray();

        /** @var UserObject[] $authors */
        $authors = $post->authors->map(function ($author) use ($blog) {
            return new UserObject($author, $blog);
        })->toArray();


        $this->variants = $variants;
        $this->tags = $tags;
        $this->authors = $authors;
    }
}
