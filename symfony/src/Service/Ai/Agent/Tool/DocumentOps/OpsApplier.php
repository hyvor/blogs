<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use App\Service\Post\Content\Markdown\MarkdownParser;
use App\Service\Post\Content\PostSchema;
use Hyvor\Phrosemirror\Document\Node;

class OpsApplier
{

    private PostSchema $postSchema;

    public function __construct()
    {
        $this->postSchema = new PostSchema();
    }

    /**
     * @param Op[] $ops
     */
    public function apply(FetchedDocument $fetchedDocument, array $ops): Node
    {
        foreach ($ops as $op) {
            match (true) {
                $op instanceof OpReplace => $this->applyReplace($fetchedDocument, $op),
                $op instanceof OpInsert => $this->applyInsert($fetchedDocument, $op),
            };
        }

        return $fetchedDocument->getDocument();

    }

    private function applyReplace(FetchedDocument $fetchedDocument, OpReplace $op): void
    {
        $nodeIdMap = $fetchedDocument->getNodeIdMap();
        $document = $fetchedDocument->getDocument();

        if (!isset($nodeIdMap[$op->nodeId])) {
            return; // Node ID not found, skip this operation
        }

        $nodeToReplace = $nodeIdMap[$op->nodeId];
        $newNodes = $this->parseMarkdownToNodes($op->newContentMarkdown);

        $allNodes = $document->content->all();
        foreach ($allNodes as $index => $node) {
            if ($node === $nodeToReplace) {
                array_splice($allNodes, $index, 1, $newNodes);
                break;
            }
        }

        $document->content->setNodes($allNodes);
    }

    private function applyInsert(FetchedDocument $fetchedDocument, OpInsert $op): void
    {
        $nodeIdMap = $fetchedDocument->getNodeIdMap();
        $document = $fetchedDocument->getDocument();

        if (!isset($nodeIdMap[$op->referenceNodeId])) {
            return; // Reference Node ID not found, skip this operation
        }

        $referenceNode = $nodeIdMap[$op->referenceNodeId];
        $newNodes = $this->parseMarkdownToNodes($op->contentMarkdown);

        $allNodes = $document->content->all();
        foreach ($allNodes as $index => $node) {
            if ($node === $referenceNode) {
                if ($op->insertBefore) {
                    array_splice($allNodes, $index, 0, $newNodes);
                } else {
                    array_splice($allNodes, $index + 1, 0, $newNodes);
                }
                break;
            }
        }

        $document->content->setNodes($allNodes);
    }

    /**
     * @return Node[]
     */
    private function parseMarkdownToNodes(string $markdown): array
    {
        $markdownParser = new MarkdownParser();
        $nodesJson = $markdownParser->parse($markdown);

        return array_map(
            fn(array $json) => $this->postSchema->nodeFrom($json),
            $nodesJson
        );
    }

}
