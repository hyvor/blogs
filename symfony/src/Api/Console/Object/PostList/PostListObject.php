<?php

namespace App\Api\Console\Object\PostList;

use App\Api\Console\Object\PostVariantSummaryObject;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserVariant;
use App\Service\Route\PermalinkService;

class PostListObject
{
    public int $id;
    public int $created_at;
    public int $updated_at;
    public ?int $published_at;
    public bool $is_featured;
    public bool $is_page;

    public ?string $slug;
    public ?string $url;
    public ?string $title;
    /** @var array<string, mixed> */
    public array $link_analysis;

    /** @var PostVariantSummaryObject[] */
    public array $variants;

    /**
     * @var array<array{name: string, is_private: bool}>
     */
    public array $tags;

    /**
     * @var array<array{name: string, picture_url: ?string}>
     */
    public array $authors;

    public function __construct(
        Post $post,
        Language $language,
        PermalinkService $permalinkService,
    ) {
        $this->id = $post->getId();
        $this->created_at = $post->getCreatedAt()->getTimestamp();
        $this->updated_at = $post->getUpdatedAt()->getTimestamp();
        $this->published_at = $post->getPublishedAt()?->getTimestamp();
        $this->is_featured = $post->isFeatured();
        $this->is_page = $post->isPage();

        $variants = $post->getVariants()->toArray();

        $primaryVariant = null;
        foreach ($variants as $variant) {
            if ($variant->getLanguage()->getId() === $language->getId()) {
                $primaryVariant = $variant;
                break;
            }
        }
        $primaryVariant ??= $variants[0] ?? null;

        $this->slug = $primaryVariant?->getSlug();
        $this->url = $primaryVariant !== null ? $permalinkService->getPostVariantPermalink($primaryVariant) : null;
        $this->title = $primaryVariant?->getTitle();
        $this->link_analysis = $primaryVariant?->getLinkAnalysis() ?? [];

        usort($variants, fn($a, $b) => $a->getLanguage()->getId() <=> $b->getLanguage()->getId());
        $this->variants = array_map(fn($v) => new PostVariantSummaryObject($v), $variants);

        $this->tags = array_map(
            fn(Tag $tag) => [
                'name' => self::primaryVariantName($tag->getVariants()->toArray()),
                'is_private' => $tag->isPrivate(),
            ],
            $post->getTags()->toArray(),
        );
        $this->authors = array_map(
            fn(User $user) => [
                'name' => self::primaryVariantName($user->getVariants()->toArray()),
                'picture_url' => $user->getPictureUrl(),
            ],
            $post->getAuthors()->toArray(),
        );
    }

    /**
     * @param UserVariant $variants
     */
    private static function primaryVariantName(array $variants): string
    {
        foreach ($variants as $variant) {
            if ($variant->getLanguage()->isPrimary()) {
                return $variant->getName() ?? '';
            }
        }

        $first = array_values($variants)[0] ?? null;
        return $first?->getName() ?? '';
    }
}
