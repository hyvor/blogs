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
    public string $slug;
    public string $url;
    public string $name;
    public ?string $description;
    public int $posts_count;
    
    public function __construct(Tag $tag, Blog $blog, Language $language)
    {
        
        $variants = $tag->variants;

        $this->id = $tag->id;
        $this->slug = $tag->slug;
        $this->url = PermalinkRepository::getTagPermalink($tag, $blog);
        $this->name = VariantsHelper::getVariantValue('name', $variants, $language);
        $this->description = VariantsHelper::getVariantValue('description', $variants, $language);
        $this->posts_count = $tag->posts_count;

    }
}
