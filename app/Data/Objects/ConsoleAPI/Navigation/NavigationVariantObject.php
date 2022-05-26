<?php

namespace App\Data\Objects\ConsoleAPI\Navigation;

use App\Models\Navigation;
use App\Models\NavigationVariant;

class NavigationVariantObject
{
    public int $navigation_id;
    public int $language_id;
    public ?string $name;

    public function __construct(NavigationVariant $navigationVariant, Navigation $navigation)
    {
        $language = $navigationVariant->language;
        $this->navigation_id = $navigationVariant->navigation_id;
        $this->language_id = $language->id;
        $this->name = $navigationVariant->name;
    }
}
