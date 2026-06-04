<?php

namespace App\Api\Data\Object;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Tag;
use App\Service\Route\PermalinkService;

class TagObject
{
    public int $id;
    public string $slug;
    public ?bool $is_private;
    public ?string $name;
    public ?string $description;
    public string $url;
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
        $this->slug = $tag->getSlug();
        $this->is_private = $tag->isPrivate();
        $this->posts_count = $tag->getPostsCount() ?? 0;
        $this->url = $permalinkService->getTagPermalink($tag, $blog, $language);
        $this->language = new LanguageObject($language);

        $this->name = null;
        $this->description = null;
        foreach ($variantData as $vd) {
            if ($vd['language']->getId() === $language->getId()) {
                $this->name = $vd['name'];
                $this->description = $vd['description'];
            } else {
                $url = $permalinkService->getTagPermalink($tag, $blog, $vd['language']);
                $this->variants[] = new VariantObject(new LanguageObject($vd['language']), $url);
            }
        }
    }
}
