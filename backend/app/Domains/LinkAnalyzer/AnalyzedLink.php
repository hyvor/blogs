<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

class AnalyzedLink
{

    public function __construct(
        public string $originalUrl,
        public string $url,
        public int $status,
    ) {}

}