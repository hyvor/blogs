<?php

namespace App\Service\Import\Importer;

use App\Entity\Enum\PostVariantStatus;

class ImportingPostVariant
{
    public function __construct(
        public string $slug,
        public string $content,
        public string $title,
        public string $description,
        public ?string $languageCode = null,
        public PostVariantStatus $status = PostVariantStatus::PUBLISHED,
    ) {
    }
}
