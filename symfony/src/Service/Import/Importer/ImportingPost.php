<?php

namespace App\Service\Import\Importer;

class ImportingPost
{
    /**
     * @param ImportingPostVariant[] $variants
     */
    public function __construct(
        public \DateTimeImmutable $publishedAt,
        public bool $isPage = false,
        public bool $isFeatured = false,
        public ?string $featuredImageUrl = null,
        public array $variants = [],
    ) {
    }
}
