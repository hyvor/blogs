<?php

namespace App\Data\Objects\DataAPI;

use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Tag;

class TagObject
{
    public int $id;

    public int $created_at;

    public bool $is_private;

    public string $slug;

    public string $url;

    public string $name;

    public ?string $description;

    public ?string $code_head;
    public ?string $code_foot;

    public int $posts_count;

    public LanguageObject $language;

    /**
     * @var VariantObject[]
     */
    public array $variants;

    public function __construct(Tag $tag, Blog $blog, Language $language)
    {
        $variants = $tag->variants;

        $this->id = $tag->id;
        $this->created_at = $tag->created_at->getTimestamp();
        $this->is_private = $tag->is_private;
        $this->slug = $tag->slug;
        $this->url = PermalinkRepository::getTagPermalink($tag, $blog, $language);
        $this->name = VariantsHelper::getVariantValue('name', $variants, $language) ?? '';
        $this->description = VariantsHelper::getVariantValue('description', $variants, $language) ?? '';
        $this->posts_count = $tag->posts_count ?? 0;

        $this->code_head = $tag->code_head;
        $this->code_foot = $tag->code_foot;

        $this->language = new LanguageObject($language);

        $this->variants = $variants
            ->where('language_id', '!=', $language->id)
            ->map(function ($variant) use ($tag, $blog) {
                $variantLanguage = $variant->language;
                assert($variantLanguage instanceof Language, 'Language should be loaded');
                $url = PermalinkRepository::getTagPermalink($tag, $blog, $variantLanguage);

                return new VariantObject($variantLanguage, $url);
            })->toArray();
    }
}
