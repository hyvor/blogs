<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

class OpInsert extends Op
{

    public function __construct(
        public string $referenceNodeId,
        public string $contentMarkdown,
        public bool $insertBefore = false
    ) {}

    public function name(): string
    {
        return 'insert';
    }
}
