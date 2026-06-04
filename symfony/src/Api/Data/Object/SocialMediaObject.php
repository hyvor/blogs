<?php

namespace App\Api\Data\Object;

class SocialMediaObject
{
    public function __construct(
        public readonly ?string $facebook,
        public readonly ?string $twitter,
        public readonly ?string $linkedin,
        public readonly ?string $youtube,
        public readonly ?string $instagram,
        public readonly ?string $github,
        public readonly ?string $tiktok,
    ) {}
}
