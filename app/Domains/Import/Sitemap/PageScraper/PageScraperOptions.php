<?php declare(strict_types=1);

namespace App\Domains\Import\Sitemap\PageScraper;

class PageScraperOptions
{

    public function __construct(
        public readonly string  $contentSelector,
        public readonly ?string $titleSelector = null,
        public readonly ?string $descriptionSelector = null,
        public readonly ?string $contentExcludeSelector = null,
        public readonly ?string $publishedAtSelector = null,
        public readonly ?string $slugExclude = null,
    ) {}

}