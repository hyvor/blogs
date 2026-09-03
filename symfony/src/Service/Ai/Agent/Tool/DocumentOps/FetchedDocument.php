<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use App\Entity\PostVariant;
use Hyvor\Phrosemirror\Document\Node;

class FetchedDocument
{

    /**
     * ops that have been applied to the document so far, kept only to know whether it changed
     * @var Op[]
     */
    private array $ops = [];

    public function __construct(
        private PostVariant $postVariant,
        private Node $document,
        private NodeIdMapBuilder $nodeIdMapBuilder,
    ) {}

    public function getDocument(): Node
    {
        return $this->document;
    }

    public function getPostVariant(): PostVariant
    {
        return $this->postVariant;
    }

    /**
     * @return array<string, Node>
     */
    public function getNodeIdMap(): array
    {
        return $this->nodeIdMapBuilder->getMap();
    }

    /**
     * Assigns IDs to a node (and its addressable descendants) newly introduced into the document by an op.
     */
    public function registerNode(Node $node): void
    {
        $this->nodeIdMapBuilder->register($node);
    }

    /**
     * Removes the ID mapping for a node (and its descendants) removed from the document by an op.
     */
    public function unregisterNode(Node $node): void
    {
        $this->nodeIdMapBuilder->unregister($node);
    }

    public function applyOp(Op $op): bool
    {
        if (!new OpsApplier()->apply($this, $op)) {
            return false;
        }

        $this->ops[] = $op;
        return true;
    }

    /**
     * @return Op[]
     */
    public function getOps(): array
    {
        return $this->ops;
    }

    public function changed(): bool
    {
        return count($this->ops) > 0;
    }

}
