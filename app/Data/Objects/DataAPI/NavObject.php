<?php

namespace App\Data\Objects\DataAPI;

use App\Models\Navigation;

class NavObject
{
    public string $name;
    public string $url;

    public function __construct(Navigation $navigation)
    {
        $this->name = $navigation->name;
        $this->url = $navigation->url;
    }
}
