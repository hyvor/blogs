<?php

namespace App\Api\Data\Object;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Tag;
use App\Service\Route\PermalinkService;

class TagObject
{
    public int $id;
    public int $created_at;
    public bool $is_private;
    public string $slug;
    public string $url;
    public string $name;
    public string $description;
    public ?string $code_head;
    public ?string $code_foot;
    public int $posts_count;
    public LanguageObject $language;

    /** @var VariantObject[] */
    public array $variants = [];

    public function __construct(Tag $tag, Blog $blog, Language $language, PermalinkService $permalinkService)
    {
        $this->id = $tag->getId();
        $this->created_at = $tag->getCreatedAt()->getTimestamp();
        $this->is_private = (bool)$tag->isPrivate();
        $this->slug = $tag->getSlug();
        $this->url = $permalinkService->getTagPermalink($tag, $blog, $language);
        $this->posts_count = $tag->getPostsCount();
        $this->code_head = $tag->getCodeHead();
        $this->code_foot = $tag->getCodeFoot();
        $this->language = new LanguageObject($language);

        $variants = $tag->getVariants();
        $selectedVariant = $variants[0] ?? null;
        foreach ($variants as $variant) {
            if ($variant->getLanguage()->getId() === $language->getId()) {
                $selectedVariant = $variant;
            } else {
                $url = $permalinkService->getTagPermalink($tag, $blog, $variant->getLanguage());
                $this->variants[] = new VariantObject(new LanguageObject($variant->getLanguage()), $url);
            }
        }

        $this->name = $selectedVariant?->getName() ?? '';
        $this->description = $selectedVariant?->getDescription() ?? '';
    }
}
