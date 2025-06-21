<?php

namespace App\Domains\LinkAnalyzer\PostVariantsCheck;

use App\Models\PostVariant;

class OnStartEvent
{

    public function __construct(
        public PostVariant $variant
    ) {
    }

}