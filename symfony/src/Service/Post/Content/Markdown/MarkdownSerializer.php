<?php

namespace App\Service\Post\Content\Markdown;

use App\Service\Post\Content\Marks;
use App\Service\Post\Content\Nodes;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Document\TextNode;

class MarkdownSerializer
{

    public const SCHEMA_FOR_AI_AGENTS = <<<MD
    Inline styles (can be combined/nested): **bold**, _italic_, `code`, [link text](https://example.com), ~~strikethrough~~, ~subscript~, ^superscript^, ==highlight==

    Paragraphs: plain text, separated from each other by a blank line. A single line break (no blank line) inside a paragraph is a hard line break, not a new paragraph.

    Headings: "#" through "######" for h1-h6. Optionally end with " {#some-id}" to set a custom id, e.g. "## Title {#my-title}".

    Blockquote: prefix every line with "> ".

    Callout (a highlighted blockquote): a blockquote whose first line is "[emoji, fg=#hexcolor, bg=#hexcolor]", e.g.:
    > [💡, fg=#000000, bg=#f1f1ef]
    > Note text

    Code block: fenced with an optional language, e.g.:
    ```php
    echo 1;
    ```

    Horizontal rule: "---" alone on its own line.

    Lists: "- item" for a bullet list, "1. item" for an ordered list. Nest a sub-list by indenting it under its parent list item.

    Table (GFM pipe table, first row is always the header row):
    | Header 1 | Header 2 |
    | --- | --- |
    | Cell 1 | Cell 2 |

    Custom HTML: raw HTML as its own standalone block (not mixed with other markdown on the same block).

    Image: "![alt](https://example.com/image.png)". Optionally set a display width/height with a quoted "WxH" title - either side may be blank to only constrain one dimension, e.g. "![alt](https://example.com/image.png "100x200")" or "![alt](https://example.com/image.png "100x")".

    Rich/media blocks - each must be the only thing in its paragraph, using "![#name ...]" image syntax (never a plain link) so it can't be confused with a real link or reference:
        - Audio: ![#audio](https://example.com/audio.mp3)
        - Bookmark (link preview card): ![#bookmark](https://example.com)
        - Embed (e.g. iframe/tweet/video embed): ![#embed](https://example.com/embed)
        - Table of contents: ![#toc]()
        - Button (label goes in quotes, and can use inline styles): ![#button "Click **me**"](https://example.com)
    MD;


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
            $markType instanceof Marks\Link => '[' . $this->escapeBracketText($text) . "]({$mark->attrs->get('href', false)})",
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
            $node->type instanceof Nodes\Audio\Audio => "![#audio]({$node->attrs->get('src', false)})\n\n",
            $node->type instanceof Nodes\Image\Image => $this->imageToMarkdown($node),

            // rich
            $node->type instanceof Nodes\Bookmark\Bookmark => "![#bookmark]({$node->attrs->get('url', false)})\n\n",
            $node->type instanceof Nodes\Button\Button => $this->buttonToMarkdown($node, $children()),
            $node->type instanceof Nodes\Embed\Embed => "![#embed]({$node->attrs->get('url', false)})\n\n",

            // text
            $node->type instanceof Nodes\Paragraph,
            $node->type instanceof Nodes\CustomHtml => "{$children()}\n\n",
            $node->type instanceof Nodes\Heading\Heading => $this->headingToMarkdown($node, $children()),
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
            // an explicit (empty) destination is required so this parses as a real inline
            // image rather than a shortcut reference image, which is what "![#toc]" alone
            // would be - subject to the same global reference-definition ambiguity we're
            // using image syntax to avoid in the first place
            $node->type instanceof Nodes\Toc\Toc => "![#toc]()\n\n",

            default => throw new \LogicException('Unhandled node type: ' . $node->type::class),
        };
    }

    private function headingToMarkdown(Node $node, string $children): string
    {
        $level = str_repeat('#', (int) $node->attrs->get('level'));
        $id = $node->attrs->get('id', false);
        $idSuffix = $id ? " {#$id}" : '';

        return "$level $children$idSuffix\n\n";
    }

    private function calloutToMarkdown(Node $node, string $children): string
    {
        $icon = $node->attrs->get('emoji');
        $fg = $node->attrs->get('fg', false);
        $bg = $node->attrs->get('bg', false);

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
                $cellMarkdown = str_replace(["\r\n", "\n"], ' ', $cellMarkdown);
                $cells[] = str_replace('|', '\\|', $cellMarkdown);
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

    /**
     * The image's width/height are encoded into the markdown title as
     * "WxH" (e.g. "100x200", "100x", "x200")
     */
    private function imageToMarkdown(Node $node): string
    {
        $src = $node->attrs->get('src', false);
        $alt = $this->escapeBracketText((string) $node->attrs->get('alt'));
        $width = $node->attrs->get('width', false);
        $height = $node->attrs->get('height', false);
        $sizePart = ($width || $height) ? " \"{$width}x{$height}\"" : '';
        return "![{$alt}]({$src}$sizePart)\n\n";
    }

    /**
     * The button's label is embedded as a quoted string inside the image
     * alt text itself, e.g. ![#button "Click **me**"](https://example.com).
     * Image syntax (rather than a plain link) avoids any ambiguity with
     * CommonMark's shortcut reference-link resolution. The label supports
     * inline marks (it's rendered via $children, not plain text) -
     * MarkdownParser recovers it by stripping the marker text off the
     * first/last (always unmarked) text runs.
     */
    private function buttonToMarkdown(Node $node, string $children): string
    {
        $href = $node->attrs->get('href', false);
        $text = $this->escapeBracketText($children);

        return "![#button \"$text\"]($href)\n\n";
    }

    private function escapeBracketText(string $text): string
    {
        return str_replace(['[', ']'], ['\\[', '\\]'], $text);
    }

    private function blockquoteToMarkdown(string $children): string
    {
        $lines = explode("\n", trim($children));
        $quotedLines = array_map(fn($line) => $line === '' ? '>' : "> $line", $lines);
        return implode("\n", $quotedLines) . "\n\n";
    }

    private function codeBlockToMarkdown(Node $node, string $children): string
    {
        $language = $node->attrs->get('language');
        return "```$language\n$children\n```\n\n";
    }

}
