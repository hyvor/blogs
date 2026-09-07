<?php

namespace App\Api\Data\Object;

class MetaObject
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $featured_image,
        public readonly string $url,
        public readonly string $canonical_url,
    ) {}
}
