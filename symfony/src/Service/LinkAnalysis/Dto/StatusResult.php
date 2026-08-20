<?php

namespace App\Service\LinkAnalysis\Dto;

use App\Entity\Enum\LinkAnalyzerCheckType;
use App\Service\LinkAnalysis\StatusCheck\IgnoreReason;

class StatusResult
{
    public function __construct(
        public LinkAnalyzerCheckType $type,
        public int $httpStatus,
        public bool $ignored = false,
        public ?IgnoreReason $ignoreReason = null,
        public ?string $comment = null,
    ) {}
}
