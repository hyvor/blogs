<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

class OpReplace extends Op
{

    public function __construct(
        public string $nodeId,
        public string $newContentMarkdown,
    ) {}

    public function name(): string
    {
        return 'replace';
    }

}
