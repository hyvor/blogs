<?php

namespace App\Service\LinkAnalysis\StatusCheck;

use App\Entity\Enum\LinkAnalyzerCheckType;

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
