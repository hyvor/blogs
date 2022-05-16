<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class CollectionWithTotal
{
    public function __construct(
        public Collection $collection,
        public int $total
    ) {
    }
}
