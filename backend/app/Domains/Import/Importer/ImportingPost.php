<?php declare(strict_types=1);

namespace App\Domains\Import\Importer;

use Carbon\Carbon;

class ImportingPost
{

    /**
     * @param ImportingPostVariant[] $variants
     */
    public function __construct(
        public Carbon $publishedAt,
        public bool $isPage = false,
        public bool $isFeatured = false,
        public ?string $featuredImageUrl = null,
        public array $variants = [],
    ) {}

}