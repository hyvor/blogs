<?php

namespace App\Data\Objects\ConsoleAPI\Navigation;

use App\Models\NavigationVariant;
use App\Models\Navigation;


class NavigationVariantObject
{
    public int $language_id;
    public ?string $name;

    public function __construct(NavigationVariant $navigationVariant, Navigation $navigation)
    {
        $language = $navigationVariant->language;
        $this->language_id = $language->id;
        $this->name = $navigationVariant->name;
    }
}
