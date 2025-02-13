<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

class ExternalStatusCheckChunkDoneEvent
{

    public function __construct(
        public int $chunkSize,
        public int $chunkIndex,
        public float $durationSeconds
    ) {
    }

}