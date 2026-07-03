<?php

namespace App\Service\LinkAnalysis\StatusCheck;

class AnalyzedLink
{
    public function __construct(
        public string $originalUrl,
        public string $url,
        public StatusResult $status,
    ) {}
}