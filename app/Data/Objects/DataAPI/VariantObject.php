<?php

namespace App\Data\Objects\DataAPI;

use App\Models\Language;

class VariantObject
{
    public LanguageObject $language;
    public string $url;

    public function __construct(Language $language, string $url)
    {
        $this->language = new LanguageObject($language);
        $this->url = $url;
    }
}
