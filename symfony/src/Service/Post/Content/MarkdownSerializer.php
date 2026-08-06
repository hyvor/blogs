<?php

namespace App\Service\Post\Content;

use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Document\TextNode;
use Hyvor\Phrosemirror\Types\MarkType;

class MarkdownSerializer
{

    public function serialize(Node $node): string
    {

        $nodeType = $node->type;

        if ($nodeType->isText() && $node instanceof TextNode) {
            $text = $node->text;

            foreach (array_reverse($node->marks) as $mark) {
                $markType = $mark->type;
                $text = $this->markToMarkdown($markType, $text);
            }

            return $text;
        }

        $childrenMarkdown = '';

        foreach ($node->content as $childNode) {
            $childrenMarkdown .= $this->serialize($childNode);
        }

        return $this->nodeToMarkdown($node, $childrenMarkdown);

    }

    private function markToMarkdown(MarkType $markType, string $text): string
    {
        return match (true) {
            $markType instanceof Marks\Strong => "**$text**",
            $markType instanceof Marks\Em => "_{$text}_",
            $markType instanceof Marks\Code => "`$text`",
            $markType instanceof Marks\Link => "[{$text}]({$markType->attrs->href})",
            $markType instanceof Marks\Strike => "~~{$text}~~",
            $markType instanceof Marks\Sub => "~{$text}~",
            $markType instanceof Marks\Sup => "^{$text}^",
            $markType instanceof Marks\Highlight => "=={$text}==",
            default => $text,
        };
    }

    private function nodeToMarkdown(Node $node, string $children): string
    {
        return match (true) {
            // media
            $node->type instanceof Nodes\Audio\Audio => "[#audio]({$node->attrs->src})\n\n",
            $node->type instanceof Nodes\Image\Image => $this->imageToMarkdown($node),

            // rich
            $node->type instanceof Nodes\Bookmark\Bookmark => "[#bookmark]({$node->attrs->href})\n\n",
            $node->type instanceof Nodes\Button\Button => "[#button]({$node->attrs->href})\n\n",
            $node->type instanceof Nodes\Embed\Embed => "[#embed]({$node->attrs->url})\n\n",

            // text
            $node->type instanceof Nodes\Paragraph,
            $node->type instanceof Nodes\CustomHtml => "$children\n\n",
            $node->type instanceof Nodes\Heading\Heading => str_repeat('#', $node->attrs->level) . " $children\n\n",
            $node->type instanceof Nodes\CodeBlock\CodeBlock => $this->codeBlockToMarkdown($node, $children),

            // blockquote
            $node->type instanceof Nodes\Blockquote => $this->blockquoteToMarkdown($children),
            $node->type instanceof Nodes\Callout\Callout => $this->calloutToMarkdown($node, $children),

            // lists
            $node->type instanceof Nodes\BulletList => "- $children\n\n",
            $node->type instanceof Nodes\OrderedList => "1. $children\n\n",

            // wrappers
            $node->type instanceof Nodes\Doc,
            $node->type instanceof Nodes\Figure,
            $node->type instanceof Nodes\Figcaption,
            $node->type instanceof Nodes\ListItem
            => $children,

            $node->type instanceof Nodes\HorizontalRule => "---\n\n",

            // special
            $node->type instanceof Nodes\Toc\Toc => "[#toc]\n\n",
        };
    }

    private function calloutToMarkdown(Node $node, string $children): string
    {
        $icon = $node->attrs->icon ?? '';
        $fg = $node->attrs->fg ?? '';
        $bg = $node->attrs->bg ?? '';

        $topLine = "[$icon, fg=$fg, bg=$bg]";
        return $this->blockquoteToMarkdown("$topLine\n$children");
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
        $quotedLines = array_map(fn($line) => "> $line", $lines);
        return implode("\n", $quotedLines) . "\n\n";
    }

    private function codeBlockToMarkdown(Node $node, string $children): string
    {
        $language = $node->attrs->language ?? '';
        return "```$language\n$children\n```\n\n";
    }

}
