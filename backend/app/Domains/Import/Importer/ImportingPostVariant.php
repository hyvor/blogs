<?php declare(strict_types=1);

namespace App\Domains\Import\Importer;

use App\Data\Enums\PostStatusEnum;

class ImportingPostVariant
{

    public function __construct(
        public string $slug,
        public string $content,
        public string $title,
        public string $description,
        public ?string $languageCode = null,
        public PostStatusEnum $status = PostStatusEnum::PUBLISHED,
    ) {}

}