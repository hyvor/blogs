<?php

namespace App\Api\Data\Object;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\Route\PermalinkService;

class PostObject
{
    public int $id;
    public int $created_at;
    public int $updated_at;
    public int $published_at;
    public bool $is_featured;
    public bool $is_page;
    public string $slug;
    public string $url;
    public string $content;
    public int $words;
    public ?string $title;
    public ?string $description;
    public ?string $featured_image_url;
    public ?string $canonical_url;
    public string $code_head;
    public string $code_foot;
    public LanguageObject $language;
    /** @var VariantObject[] */
    public array $variants = [];
    /** @var TagObject[] */
    public array $tags = [];
    /** @var TagObject[] */
    public array $tags_private = [];
    /** @var AuthorObject[] */
    public array $authors = [];

    /**
     * @param Tag[] $tags
     * @param User[] $authors
     * @param array<array{variant: PostVariant, language: Language}> $otherVariants
     */
    public function __construct(
        Post $post,
        PostVariant $variant,
        Blog $blog,
        Language $language,
        PermalinkService $permalinkService,
        array $tags = [],
        array $authors = [],
        array $otherVariants = [],
    ) {
        $this->id = $post->getId();
        $this->created_at = $post->getCreatedAt()->getTimestamp();
        $variantUpdatedAt = $variant->getUpdatedAt();
        $this->updated_at = ($variantUpdatedAt ?? $post->getUpdatedAt())->getTimestamp();
        $this->published_at = ($post->getPublishedAt() ?? $post->getCreatedAt())->getTimestamp();
        $this->is_featured = $post->isFeatured();
        $this->is_page = $post->isPage();
        $this->slug = $variant->getSlug() ?? '';
        $this->url = $permalinkService->getPostPermalink($post, $blog, $language);
        $this->content = $variant->getContentHtml() ?? '';
        $this->words = $variant->getWords() ?? 0;
        $this->title = $variant->getTitle();
        $this->description = $variant->getDescription();
        $this->featured_image_url = $post->getFeaturedImageUrl();
        $this->canonical_url = $post->getCanonicalUrl();
        $this->code_head = $post->getCodeHead() ?? '';
        $this->code_foot = $post->getCodeFoot() ?? '';
        $this->language = new LanguageObject($language);

        foreach ($tags as $tag) {
            $tagObj = new TagObject($tag, $blog, $language, $permalinkService);
            if ($tag->isPrivate()) {
                $this->tags_private[] = $tagObj;
            } else {
                $this->tags[] = $tagObj;
            }
        }

        foreach ($authors as $user) {
            $this->authors[] = new AuthorObject($user, $blog, $language, $permalinkService);
        }

        foreach ($otherVariants as $ov) {
            $otherVariantLang = $ov['language'];
            $url = $permalinkService->getPostPermalink($post, $blog, $otherVariantLang);
            $this->variants[] = new VariantObject(new LanguageObject($otherVariantLang), $url);
        }
    }
}
