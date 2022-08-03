<?php

namespace App\Domains\Delivery\Processors\Sitemap;

use Carbon\Carbon;

class IndexEntry
{
    // in the future, add last mod
    public function __construct(public string $name/*, public Carbon $lastMod*/)
    {
    }
}
