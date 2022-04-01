<?php

namespace App\Data\Objects\ConsoleAPI\Blog;

use App\Models\BlogVariant;
use App\Models\Blog;

class BlogVariantObject
{
    public int $id;
    public int $blog_id;
    public int $language_id;
    public string $name;
    public ?string $description;


    public function __construct(BlogVariant $blogVariant)
    {
        $language = $blogVariant->language;

        $this->id = $blogVariant->id;
        $this->tag_id = $blogVariant->tag_id;

        $this->language_id = $language->id;

        $this->name = $blogVariant->name;
        $this->description = $blogVariant->description;
    }
} 
