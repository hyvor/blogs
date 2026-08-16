<?php

namespace App\Service\LinkAnalysis\Dto;

class AnalyzedLink
{
    public function __construct(
        public string $originalUrl,
        public string $url,
        public StatusResult $status,
    ) {}
}