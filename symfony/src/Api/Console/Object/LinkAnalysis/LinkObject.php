<?php

namespace App\Api\Console\Object\LinkAnalysis;

use App\Entity\Enum\LinkAnalyzerLinkStatus;

class LinkObject
{
    public function __construct(
        public int $id,
        public string $url,
        public string $full_url,
        public int $status_code,
        public LinkAnalyzerLinkStatus $status_type,
        public bool $ignored,
        public ?string $ignore_reason,
        public ?string $comment,
        public int $post_id,
        public int $post_variant_id,
        public int $post_variant_language_id,
        public ?string $post_variant_title,
        public string $post_variant_url,
    ) {}
}
