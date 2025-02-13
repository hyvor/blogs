<?php

namespace App\Domains\LinkAnalyzer\Check;

class ResolvedUrl
{

    public function __construct(
        public string $originalUrl,
        // status code is taken from this URL
        public string $fullUrl,
    ) {
    }

}