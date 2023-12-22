<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\BlogVariant;
use App\Models\Language;

class BlogVariantObject
{
    public int $id;

    public int $language_id;

    public ?string $name;

    public ?string $description;

    public function __construct(BlogVariant $blogVariant)
    {
        /** @var Language $language */
        $language = $blogVariant->language;

        $this->language_id = $language->id;

        $this->name = $blogVariant->name;
        $this->description = $blogVariant->description;
    }
}
