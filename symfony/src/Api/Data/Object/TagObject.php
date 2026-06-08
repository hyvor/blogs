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

    /**
     * @param array<array{language: Language, name: ?string, description: ?string}> $variantData
     */
    public function __construct(Tag $tag, Blog $blog, Language $language, PermalinkService $permalinkService, array $variantData = [])
    {
        $this->id = $tag->getId();
        $this->created_at = $tag->getCreatedAt()->getTimestamp();
        $this->is_private = (bool)$tag->isPrivate();
        $this->slug = $tag->getSlug();
        $this->url = $permalinkService->getTagPermalink($tag, $blog, $language);
        $this->posts_count = $tag->getPostsCount() ?? 0;
        $this->code_head = $tag->getCodeHead();
        $this->code_foot = $tag->getCodeFoot();
        $this->language = new LanguageObject($language);

        $matched = null;
        $fallback = $variantData[0] ?? null;
        foreach ($variantData as $vd) {
            if ($vd['language']->getId() === $language->getId()) {
                $matched = $vd;
            } else {
                $url = $permalinkService->getTagPermalink($tag, $blog, $vd['language']);
                $this->variants[] = new VariantObject(new LanguageObject($vd['language']), $url);
            }
        }

        $resolved = $matched ?? $fallback;
        $this->name = $resolved['name'] ?? '';
        $this->description = $resolved['description'] ?? '';
    }
}
