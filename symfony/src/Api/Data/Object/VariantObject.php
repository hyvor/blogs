<?php

namespace App\Api\Data\Object;

class VariantObject
{
    public function __construct(
        public readonly LanguageObject $language,
        public readonly string $url,
    ) {}
}
