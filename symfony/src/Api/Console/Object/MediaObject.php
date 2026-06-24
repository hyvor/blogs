<?php

namespace App\Api\Console\Object;

class MediaObject
{
    public function __construct(
        public int $id,
        public ?int $post_id,
        public int $uploaded_at,
        public string $url,
        public string $name,
        public string $original_name,
        public ?string $extension,
    ) {}
}
