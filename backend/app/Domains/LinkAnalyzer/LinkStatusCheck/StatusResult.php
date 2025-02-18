<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

class StatusResult
{

    public function __construct(
        public StatusCheckType $type,
        public int $httpStatus,
        public bool $ignored = false,
        public ?IgnoreReasonEnum $ignoreReason = null, // if ignored
        public ?string $comment = null,
    ) {
    }

}