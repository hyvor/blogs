<?php

namespace App\Data\Objects\ConsoleAPI\Tag;

use App\Domains\Route\PermalinkRepository;
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\Tag;
use App\Models\TagVariant;

class TagVariantObject
{
    public int $language_id;

    public string $url;

    public ?string $name;

    public ?string $description;

    public function __construct(TagVariant $variant, Tag $tag, Blog $blog)
    {
        $language = $variant->language;

        if (!$language) {
            throw new SafetyException('TagVariantObject: Language not found');
        }

        $this->language_id = $language->id;

        $this->url = PermalinkRepository::getTagPermalink($tag, $blog, $language);

        $this->name = $variant->name;
        $this->description = $variant->description;
    }
}
