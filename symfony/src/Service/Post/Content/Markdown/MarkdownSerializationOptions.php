<?php

namespace App\Service\Post\Content\Markdown;

use Hyvor\Phrosemirror\Document\Node;

class MarkdownSerializationOptions
{

    public function __construct(
        /**
         * an ID to prepend to the node (used for the AI agent)
         * if ID is 'p-1', then the node will have #[p-1] prepended to it
         * @var array<string, Node>
         */
        private array $nodeIdMap = [],
    ) {}

    public function getNodeId(Node $node): ?string
    {
        return array_find_key($this->nodeIdMap, fn($mappedNode) => $mappedNode === $node);
    }

}
