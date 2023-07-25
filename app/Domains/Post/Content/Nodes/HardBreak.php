<?php

namespace App\Domains\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class HardBreak extends NodeType
{

    public string $name = 'hard_break';
    public string $group = 'inline';
    public bool $inline = true;

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'br')
        ];
    }

    public function toHtml(Node $node, string $children): string
    {
        return "<br>";
    }

}