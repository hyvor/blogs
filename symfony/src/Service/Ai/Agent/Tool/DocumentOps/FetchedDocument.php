<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use Hyvor\Phrosemirror\Document\Node;

class FetchedDocument
{

    /**
     * @var Op[]
     */
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

    /**
     * @return array<string, Node>
     */
    public function getNodeIdMap(): array
    {
        return $this->nodeIdMap;
    }

    public function addOp(Op $op): void
    {
        $this->ops[] = $op;
    }

    /**
     * @return Op[]
     */
    public function getOps(): array
    {
        return $this->ops;
    }

}
