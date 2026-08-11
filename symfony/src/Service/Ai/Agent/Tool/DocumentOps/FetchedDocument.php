<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use Hyvor\Phrosemirror\Document\Node;

class FetchedDocument
{

    private array $ops = [];

    public function __construct(
        private Node $document,

        /**
         * @var array<string, Node>
         */
        private array $nodeIdMap
    ) {}

    public function getDocument(): Node
    {
        return $this->document;
    }

    public function getNodeIdMap(): array
    {
        return $this->nodeIdMap;
    }

    public function addOp(Op $op): void
    {
        $this->ops[] = $op;
    }

    public function getOps(): array
    {
        return $this->ops;
    }

}
