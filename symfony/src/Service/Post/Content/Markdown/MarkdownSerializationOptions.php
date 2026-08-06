<?php

namespace App\Service\Post\Content\Markdown;

use Hyvor\Phrosemirror\Document\Node;

class MarkdownSerializationOptions
{

    public function __construct(
        /**
         * an ID to prepend to the node (used for the AI agent)
         * if ID is 'p-1', then the node will have #[p-1] prepended to it
         * @var array<array{node: Node, id: string}>
         */
        private array $nodeIdMap = [],
    ) {}

    public function getNodeId(Node $node): ?string
    {
        foreach ($this->nodeIdMap as $map) {
            if ($map['node'] === $node) {
                return $map['id'];
            }
        }

        return null;
    }

}
