<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\Post;

class PostObject
{
    public int $id;
    public string $preview_id;
    public int $created_at;
    public int $updated_at;
    public ?int $published_at;
    public bool $is_featured;
    public bool $is_page;
    public ?string $featured_image_url;
    public ?string $canonical_url;
    public ?string $code_head;
    public ?string $code_foot;

    /** @var PostVariantStatusObject[] */
    public array $variant_statuses;

    /** @var TagObject[] */
    public array $tags;
    /** @var UserObject[] */
    public array $authors;

    public function __construct(
        Post $post,
        Blog $blog,
        TagObjectFactory $tagObjectFactory,
        UserObjectFactory $userObjectFactory,
        string $previewId
    ) {
        $this->id = $post->getId();
        $this->preview_id = $previewId;
        $this->created_at = $post->getCreatedAt()->getTimestamp();
        $this->updated_at = $post->getUpdatedAt()->getTimestamp();
        $this->published_at = $post->getPublishedAt()?->getTimestamp();
        $this->is_featured = $post->isFeatured();
        $this->is_page = $post->isPage();
        $this->featured_image_url = $post->getFeaturedImageUrl();
        $this->canonical_url = $post->getCanonicalUrl();
        $this->code_head = $post->getCodeHead();
        $this->code_foot = $post->getCodeFoot();

        $variants = $post->getVariants()->toArray();
        usort($variants, fn($a, $b) => $a->getLanguage()->getId() <=> $b->getLanguage()->getId());
        $this->variant_statuses = array_map(fn($v) => new PostVariantStatusObject($v), $variants);

        $this->tags = array_map(fn($tag) => $tagObjectFactory->create($tag, $blog), $post->getTags()->toArray());
        $this->authors = array_map(fn($user) => $userObjectFactory->create($user, $blog), $post->getAuthors()->toArray());
    }
}
