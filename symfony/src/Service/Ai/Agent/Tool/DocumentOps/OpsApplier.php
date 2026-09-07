<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use App\Service\Post\Content\Markdown\MarkdownParser;
use Hyvor\Phrosemirror\Document\Fragment;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Document\TextNode;

class OpsApplier
{

    /**
     * Applies a single op to the document immediately, updating the FetchedDocument's node ID map
     * for any node added or removed in the process. Returns whether the op was actually applied
     * (false if the node ID(s) it referenced no longer exist in the document).
     */
    public function apply(FetchedDocument $fetchedDocument, Op $op): bool
    {
        return match (true) {
            $op instanceof OpReplace => $this->applyReplace($fetchedDocument, $op),
            $op instanceof OpInsert => $this->applyInsert($fetchedDocument, $op),
            $op instanceof OpReplaceText => $this->applyReplaceText($fetchedDocument, $op),
            $op instanceof OpDelete => $this->applyDelete($fetchedDocument, $op),
            default => throw new \LogicException('Unhandled op type: ' . $op::class),
        };
    }

    private function applyReplace(FetchedDocument $fetchedDocument, OpReplace $op): bool
    {
        $nodeIdMap = $fetchedDocument->getNodeIdMap();
        $document = $fetchedDocument->getDocument();

        if (!isset($nodeIdMap[$op->nodeId])) {
            return false; // Node ID not found, skip this operation
        }

        $nodeToReplace = $nodeIdMap[$op->nodeId];
        $fragment = $this->findParentFragment($document, $nodeToReplace);

        if ($fragment === null) {
            return false;
        }

        $newNodes = $this->parseMarkdownToNodes($op->newContentMarkdown);

        $allNodes = $fragment->all();
        foreach ($allNodes as $index => $node) {
            if ($node === $nodeToReplace) {
                array_splice($allNodes, $index, 1, $newNodes);
                break;
            }
        }

        $fragment->setNodes($allNodes);

        $fetchedDocument->unregisterNode($nodeToReplace);
        foreach ($newNodes as $newNode) {
            $fetchedDocument->registerNode($newNode);
        }

        return true;
    }

    private function applyInsert(FetchedDocument $fetchedDocument, OpInsert $op): bool
    {
        $nodeIdMap = $fetchedDocument->getNodeIdMap();
        $document = $fetchedDocument->getDocument();

        if (!isset($nodeIdMap[$op->referenceNodeId])) {
            return false; // Reference Node ID not found, skip this operation
        }

        $referenceNode = $nodeIdMap[$op->referenceNodeId];
        $fragment = $this->findParentFragment($document, $referenceNode);

        if ($fragment === null) {
            return false;
        }

        $newNodes = $this->parseMarkdownToNodes($op->contentMarkdown);

        $allNodes = $fragment->all();
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

        $fragment->setNodes($allNodes);

        foreach ($newNodes as $newNode) {
            $fetchedDocument->registerNode($newNode);
        }

        return true;
    }

    private function applyDelete(FetchedDocument $fetchedDocument, OpDelete $op): bool
    {
        $nodeIdMap = $fetchedDocument->getNodeIdMap();
        $document = $fetchedDocument->getDocument();

        if (!isset($nodeIdMap[$op->nodeId])) {
            return false; // Node ID not found, skip this operation
        }

        $nodeToDelete = $nodeIdMap[$op->nodeId];
        $fragment = $this->findParentFragment($document, $nodeToDelete);

        if ($fragment === null) {
            return false;
        }

        $allNodes = $fragment->all();
        foreach ($allNodes as $index => $node) {
            if ($node === $nodeToDelete) {
                array_splice($allNodes, $index, 1);
                break;
            }
        }

        $fragment->setNodes($allNodes);

        $fetchedDocument->unregisterNode($nodeToDelete);

        return true;
    }

    private function applyReplaceText(FetchedDocument $fetchedDocument, OpReplaceText $op): bool
    {
        $nodeIdMap = $fetchedDocument->getNodeIdMap();

        if (!isset($nodeIdMap[$op->nodeId])) {
            return false; // Node ID not found, skip this operation
        }

        $node = $nodeIdMap[$op->nodeId];
        $remaining = $op->limit;

        $node->traverse(function (Node $current) use ($op, &$remaining) {
            if ($remaining <= 0 || !$current instanceof TextNode) {
                return;
            }

            $replaced = 0;
            $current->text = $this->replaceWithLimit($current->text, $op->search, $op->replace, $remaining, $replaced);
            $remaining -= $replaced;
        });

        return true;
    }

    private function replaceWithLimit(string $subject, string $search, string $replace, int $limit, int &$count): string
    {
        $count = 0;

        if ($search === '' || $limit <= 0) {
            return $subject;
        }

        $result = '';
        $offset = 0;

        while ($count < $limit && ($pos = strpos($subject, $search, $offset)) !== false) {
            $result .= substr($subject, $offset, $pos - $offset) . $replace;
            $offset = $pos + strlen($search);
            $count++;
        }

        $result .= substr($subject, $offset);

        return $result;
    }

    private function findParentFragment(Node $root, Node $target): ?Fragment
    {
        foreach ($root->content->all() as $child) {
            if ($child === $target) {
                return $root->content;
            }

            $found = $this->findParentFragment($child, $target);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @return Node[]
     */
    private function parseMarkdownToNodes(string $markdown): array
    {
        $markdownParser = new MarkdownParser();
        return $markdownParser->parse($markdown);
    }

}
