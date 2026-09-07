<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

class OpDelete extends Op
{

    public function __construct(
        public string $nodeId,
    ) {}

    public function name(): string
    {
        return 'delete';
    }

}
