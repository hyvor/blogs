<?php

namespace App\Api\Data\Object;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Language;
use App\Entity\Post;
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

    public function __construct(
        Blog $blog,
        Post $post,
        Language $language,
        PermalinkService $permalinkService,
    ) {
       
        $variant = null;
        foreach ($post->getVariants() as $pv) {
            if ($pv->getLanguage()->getId() === $language->getId()) {
                $variant = $pv;
                continue;
            }
            if ($pv->getStatus() !== PostVariantStatus::PUBLISHED) {
                continue;
            }
            $variantLang = $pv->getLanguage();
            $url = $permalinkService->getPostPermalink($post, $blog, $variantLang);
            $this->variants[] = new VariantObject(new LanguageObject($variantLang), $url);
        }

        assert($variant !== null, 'Caller should ensure that variant is not null');
        assert($variant->getStatus() === PostVariantStatus::PUBLISHED, 'Caller should ensure that variant is published');

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

        foreach ($post->getTags() as $tag) {
            $tagObj = new TagObject($tag, $blog, $language, $permalinkService);
            if ($tag->isPrivate()) {
                $this->tags_private[] = $tagObj;
            } else {
                $this->tags[] = $tagObj;
            }
        }

        foreach ($post->getAuthors() as $user) {
            $this->authors[] = new AuthorObject($user, $blog, $language, $permalinkService);
        }

    }
}
