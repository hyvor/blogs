<?php

namespace App\Service\Post\Content\Markdown;

use App\Service\Post\Content\Marks;
use App\Service\Post\Content\Nodes;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Document\TextNode;

class MarkdownSerializer
{

    public function serialize(Node $node, MarkdownSerializationOptions $options = new MarkdownSerializationOptions): string
    {
        $nodeType = $node->type;

        if ($nodeType->isText() && $node instanceof TextNode) {
            $text = $node->text;

            foreach (array_reverse($node->marks) as $mark) {
                $text = $this->markToMarkdown($mark, $text);
            }

            return $text;
        }

        $getChildrenMarkdown = function() use ($node, $options) {
            $childrenMarkdown = '';

            foreach ($node->content as $childNode) {
                $childrenMarkdown .= $this->serialize($childNode, $options);
            }

            return $childrenMarkdown;
        };

        $nodeMarkdown = $this->nodeToMarkdown($node, $getChildrenMarkdown, $options);

        $nodeId = $options->getNodeId($node);
        if ($nodeId !== null) {
            $nodeMarkdown = "#[$nodeId] $nodeMarkdown";
        }

        return $nodeMarkdown;
    }

    private function markToMarkdown(Mark $mark, string $text): string
    {
        $markType = $mark->type;

        return match (true) {
            $markType instanceof Marks\Strong => "**$text**",
            $markType instanceof Marks\Em => "_{$text}_",
            $markType instanceof Marks\Code => "`$text`",
            $markType instanceof Marks\Link => "[{$text}]({$mark->attrs->href})",
            $markType instanceof Marks\Strike => "~~{$text}~~",
            $markType instanceof Marks\Sub => "~{$text}~",
            $markType instanceof Marks\Sup => "^{$text}^",
            $markType instanceof Marks\Highlight => "=={$text}==",
            default => $text,
        };
    }

    /**
     * @param callable(): string $children
     */
    private function nodeToMarkdown(Node $node, callable $children, MarkdownSerializationOptions $options): string
    {

        return match (true) {
            // media
            $node->type instanceof Nodes\Audio\Audio => "[#audio]({$node->attrs->src})\n\n",
            $node->type instanceof Nodes\Image\Image => $this->imageToMarkdown($node),

            // rich
            $node->type instanceof Nodes\Bookmark\Bookmark => "[#bookmark]({$node->attrs->url})\n\n",
            $node->type instanceof Nodes\Button\Button => "[#button]({$node->attrs->href})\n\n",
            $node->type instanceof Nodes\Embed\Embed => "[#embed]({$node->attrs->url})\n\n",

            // text
            $node->type instanceof Nodes\Paragraph,
            $node->type instanceof Nodes\CustomHtml => "{$children()}\n\n",
            $node->type instanceof Nodes\Heading\Heading => str_repeat('#', $node->attrs->level) . " {$children()}\n\n",
            $node->type instanceof Nodes\CodeBlock\CodeBlock => $this->codeBlockToMarkdown($node, $children()),

            // blockquote
            $node->type instanceof Nodes\Blockquote => $this->blockquoteToMarkdown($children()),
            $node->type instanceof Nodes\Callout\Callout => $this->calloutToMarkdown($node, $children()),

            // lists
            $node->type instanceof Nodes\BulletList => $this->listToMarkdown($node, ordered: false, options: $options),
            $node->type instanceof Nodes\OrderedList => $this->listToMarkdown($node, ordered: true, options: $options),

            // table
            $node->type instanceof Nodes\Table\Table => $this->tableToMarkdown($node, $options),
            $node->type instanceof Nodes\Table\TableRow,
            $node->type instanceof Nodes\Table\TableCell\TableCell,
            $node->type instanceof Nodes\Table\TableCell\TableHeader
            => $children(),

            // wrappers
            $node->type instanceof Nodes\Doc,
            $node->type instanceof Nodes\Figure,
            $node->type instanceof Nodes\Figcaption,
            $node->type instanceof Nodes\ListItem
            => $children(),

            $node->type instanceof Nodes\HorizontalRule => "---\n\n",
            $node->type instanceof Nodes\HardBreak => "\n",

            // special
            $node->type instanceof Nodes\Toc\Toc => "[#toc]\n\n",
        };
    }

    private function calloutToMarkdown(Node $node, string $children): string
    {
        $icon = $node->attrs->emoji ?? '';
        $fg = $node->attrs->fg ?? '';
        $bg = $node->attrs->bg ?? '';

        $topLine = "[$icon, fg=$fg, bg=$bg]";
        return $this->blockquoteToMarkdown("$topLine\n$children");
    }

    private function listToMarkdown(Node $node, bool $ordered, MarkdownSerializationOptions $options): string
    {
        $lines = [];

        foreach ($node->content as $index => $item) {
            $marker = $ordered ? ($index + 1) . '.' : '-';
            $indent = str_repeat(' ', mb_strlen($marker) + 1);

            $itemLines = explode("\n", trim($this->serialize($item, $options)));
            foreach ($itemLines as $lineIndex => $line) {
                if ($lineIndex === 0) {
                    $lines[] = "$marker $line";
                } elseif ($line !== '') {
                    // blank lines between blocks (e.g. after a paragraph) are dropped
                    // so a tight list item doesn't get split apart by them
                    $lines[] = "$indent$line";
                }
            }
        }

        return implode("\n", $lines) . "\n\n";
    }

    /**
     * The first row is assumed to be the header row (it's made up of
     * table_header cells), as is the case for every table this schema
     * can produce.
     */
    private function tableToMarkdown(Node $node, MarkdownSerializationOptions $options): string
    {
        $rows = [];

        foreach ($node->content as $row) {
            $cells = [];

            foreach ($row->content as $cell) {
                $cellMarkdown = trim($this->serialize($cell, $options));
                $cells[] = str_replace(["\r\n", "\n"], ' ', $cellMarkdown);
            }

            $rows[] = $cells;
        }

        if (!$rows) {
            return '';
        }

        $lines = ['| ' . implode(' | ', $rows[0]) . ' |'];
        $lines[] = '| ' . implode(' | ', array_fill(0, count($rows[0]), '---')) . ' |';

        foreach (array_slice($rows, 1) as $row) {
            $lines[] = '| ' . implode(' | ', $row) . ' |';
        }

        return implode("\n", $lines) . "\n\n";
    }

    private function imageToMarkdown(Node $node): string
    {
        $src = $node->attrs->src ?? '';
        $alt = $node->attrs->alt ?? '';
        $width = $node->attrs->width ?? '';
        $height = $node->attrs->height ?? '';
        $sizePart = ($width || $height) ? " ={$width}x{$height}" : '';
        return "![{$alt}]({$src}$sizePart)\n\n";
    }

    private function blockquoteToMarkdown(string $children): string
    {
        $lines = explode("\n", trim($children));
        $quotedLines = array_map(fn($line) => $line === '' ? '>' : "> $line", $lines);
        return implode("\n", $quotedLines) . "\n\n";
    }

    private function codeBlockToMarkdown(Node $node, string $children): string
    {
        $language = $node->attrs->language ?? '';
        return "```$language\n$children\n```\n\n";
    }

}
