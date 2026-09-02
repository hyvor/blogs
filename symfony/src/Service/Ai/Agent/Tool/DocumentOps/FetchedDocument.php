<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use Hyvor\Phrosemirror\Document\Node;

class FetchedDocument
{

    /**
     * ops that have been applied to the document so far, kept only to know whether it changed
     * @var Op[]
     */
    private array $ops = [];

    public function __construct(
        private Node $document,
        private NodeIdMapBuilder $nodeIdMapBuilder,
        // the post variant's content_unsaved_version at the moment this document was first
        // fetched - captured so a persisted document_change event can record what version the
        // agent was working from
        private int $postVariantVersion = 0,
    ) {}

    public function getDocument(): Node
    {
        return $this->document;
    }

    public function getPostVariantVersion(): int
    {
        return $this->postVariantVersion;
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
