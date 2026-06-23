<?php

namespace App\Api\Console\Object;

use App\Entity\Blog;
use App\Entity\Post;
use App\Service\Route\PermalinkService;

class PostObject
{
    public int $id;
    public int $created_at;
    public int $updated_at;
    public ?int $published_at;
    public bool $is_featured;
    public bool $is_page;
    public ?string $featured_image_url;
    public ?string $canonical_url;
    public ?string $code_head;
    public ?string $code_foot;
    /** @var PostVariantObject[] */
    public array $variants;
    /** @var TagObject[] */
    public array $tags;
    /** @var UserObject[] */
    public array $authors;

    public function __construct(Post $post, Blog $blog, PermalinkService $permalinkService, TagObjectFactory $tagObjectFactory, UserObjectFactory $userObjectFactory)
    {
        $this->id = $post->getId();
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
        $this->variants = array_map(
            fn($v) => new PostVariantObject($v, $post, $blog, $permalinkService),
            $variants,
        );

        $this->tags = array_map(fn($tag) => $tagObjectFactory->create($tag, $blog), $post->getTags()->toArray());
        $this->authors = array_map(fn($user) => $userObjectFactory->create($user, $blog), $post->getAuthors()->toArray());
    }
}
