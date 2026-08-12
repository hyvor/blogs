<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use Hyvor\Phrosemirror\Document\Node;
use App\Service\Post\Content\Nodes;

class NodeIdMapBuilder
{

    /**
     * @return array<string, Node>
     */
    public static function build(Node $node): array
    {
        $nodeIdMap = [];
        $counts = [];

        $node->traverse(function (Node $node) use (&$nodeIdMap, &$counts) {
            $prefix = self::getPrefix($node);
            if ($prefix === null) {
                return;
            }

            $counts[$prefix] = ($counts[$prefix] ?? 0) + 1;
            $nodeId = "$prefix-{$counts[$prefix]}";

            $nodeIdMap[$nodeId] = $node;
        });

        return $nodeIdMap;
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
