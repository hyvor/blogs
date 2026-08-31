<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use Hyvor\Phrosemirror\Document\Node;
use App\Service\Post\Content\Nodes;

/**
 * Assigns stable, ever-increasing node IDs (e.g. "p-1", "p-2") to addressable nodes in a document.
 *
 * One instance is owned by a single FetchedDocument for its whole lifetime. As ops are applied to
 * the document, newly created nodes are registered here so they get their own ID, and nodes removed
 * from the document are unregistered. Per-prefix counters never reset or decrease, so an ID that was
 * ever used is never reused for a different (e.g. later-created) node.
 */
class NodeIdMapBuilder
{

    /**
     * @var array<string, Node>
     */
    private array $nodeIdMap = [];

    /**
     * @var array<string, int>
     */
    private array $counts = [];

    /**
     * Registers the given node and all of its addressable descendants that don't already have an ID.
     * Used both for the initial full-document build and to assign IDs to nodes newly introduced by an op.
     */
    public function register(Node $node): void
    {
        $node->traverse(function (Node $current) {
            if ($this->idFor($current) !== null) {
                return;
            }

            $prefix = self::getPrefix($current);
            if ($prefix === null) {
                return;
            }

            $this->counts[$prefix] = ($this->counts[$prefix] ?? 0) + 1;
            $nodeId = "$prefix-{$this->counts[$prefix]}";

            $this->nodeIdMap[$nodeId] = $current;
        });
    }

    /**
     * Removes the mapping for the given node and its descendants, e.g. after it is deleted or replaced.
     * Counters are not decremented, so a future new node is never assigned an ID that used to belong to
     * a since-removed node.
     */
    public function unregister(Node $node): void
    {
        $node->traverse(function (Node $current) {
            $id = $this->idFor($current);
            if ($id !== null) {
                unset($this->nodeIdMap[$id]);
            }
        });
    }

    private function idFor(Node $node): ?string
    {
        return array_find_key($this->nodeIdMap, fn($mapped) => $mapped === $node);
    }

    /**
     * @return array<string, Node>
     */
    public function getMap(): array
    {
        return $this->nodeIdMap;
    }

    private static function getPrefix(Node $node): ?string
    {
        $nodeType = $node->type;
        return match (true) {
            // media
            $nodeType instanceof Nodes\Audio\Audio => 'audio',
            $nodeType instanceof Nodes\Image\Image => 'img',

            // rich
            $nodeType instanceof Nodes\Bookmark\Bookmark => 'bookmark',
            $nodeType instanceof Nodes\Button\Button => 'button',
            $nodeType instanceof Nodes\Embed\Embed => 'embed',

            // text
            $nodeType instanceof Nodes\Paragraph => 'p',
            $nodeType instanceof Nodes\CustomHtml => 'html',
            $nodeType instanceof Nodes\Heading\Heading => 'h',
            $nodeType instanceof Nodes\CodeBlock\CodeBlock => 'code',

            // blockquote
            $nodeType instanceof Nodes\Blockquote => 'quote',
            $nodeType instanceof Nodes\Callout\Callout => 'callout',

            // lists
            $nodeType instanceof Nodes\BulletList => 'ul',
            $nodeType instanceof Nodes\OrderedList => 'ol',
            $nodeType instanceof Nodes\ListItem => 'li',

            // table
            $nodeType instanceof Nodes\Table\Table => 'table',
            $nodeType instanceof Nodes\Table\TableRow => 'tr',
            $nodeType instanceof Nodes\Table\TableCell\TableCell => 'td',
            $nodeType instanceof Nodes\Table\TableCell\TableHeader => 'th',

            $nodeType instanceof Nodes\HorizontalRule => 'hr',

            // special
            $nodeType instanceof Nodes\Toc\Toc => 'toc',

            // wrappers and inline nodes (doc, figure, figcaption, hard break, text)
            // are not individually addressable, so they get no ID
            default => null,
        };
    }

}
