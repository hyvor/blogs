<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

class OpReplaceText extends Op
{

    public function __construct(
        public string $nodeId,
        public string $search,
        public string $replace,
        public int $limit = 1,
    ) {}

    public function name(): string
    {
        return 'replace_text';
    }

}
