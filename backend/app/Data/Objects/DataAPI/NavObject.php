<?php

namespace App\Data\Objects\DataAPI;

use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Models\Language;
use App\Models\Navigation;

class NavObject
{
    public string $name;

    public string $url;

    public function __construct(Navigation $navigation, Language $language)
    {
        $this->name = VariantsHelper::getVariantValue('name', $navigation->variants, $language) ?? '';
        $this->url = $navigation->url;
    }
}
