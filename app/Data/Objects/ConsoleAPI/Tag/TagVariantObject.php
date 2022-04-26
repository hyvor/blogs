<?php

namespace App\Data\Objects\ConsoleAPI\Tag;

use App\Models\TagVariant;
use App\Models\Tag;
use App\Models\Language;
use App\Models\Blog;

class TagVariantObject
{
    public int $id;
    public int $tag_id;
    public int $language_id;
    public ?string $name;
    public ?string $description;


    public function __construct(TagVariant $tagVariant)
    {
        $language = $tagVariant->language;

        // dd($language->id);
        $this->id = $tagVariant->id;
        $this->tag_id = $tagVariant->tag_id;

        $this->language_id = $language->id;

        $this->name = $tagVariant->name;
        $this->description = $tagVariant->description;
    }
} 
