<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

use App\Domains\LinkAnalyzer\LinkStatusCheck\StatusResult;

class AnalyzedLinkDto
{

    public function __construct(
        public string $originalUrl,
        public string $url,
        public StatusResult $status,
    ) {
    }

}