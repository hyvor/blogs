<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\BlogVariant;

class BlogVariantObject
{
    public int $id;
    public int $blog_id;
    public int $language_id;
    public ?string $name;
    public ?string $description;


    public function __construct(BlogVariant $blogVariant)
    {
        $language = $blogVariant->language;

        $this->language_id = $language->id;

        $this->name = $blogVariant->name;
        $this->description = $blogVariant->description;
    }
}
