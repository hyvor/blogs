<?php

namespace App\Service\LinkAnalysis\Dto;

class ResolvedUrl
{
    public function __construct(
        public string $originalUrl,
        // status code is taken from this URL
        public string $fullUrl,
    ) {}
}