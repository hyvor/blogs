<?php
namespace App\Data\Objects\ConsoleAPI\Tag;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Tag;
use App\Models\TagVariant;

class TagVariantObject
{

    public int $language_id;
    public string $url;
    public ?string $name;
    public ?string $description;


    public function __construct(TagVariant $tagVariant, Tag $tag, Blog $blog)
    {
        $language = $tagVariant->language;

        $this->language_id = $language->id;

        $this->url = PermalinkRepository::getTagPermalink($tag, $blog, $language);

        $this->name = $tagVariant->name;
        $this->description = $tagVariant->description;
    }
} 
