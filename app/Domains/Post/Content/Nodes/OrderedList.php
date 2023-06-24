<?php

namespace App\Domains\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class OrderedList extends NodeType
{

    public string $name = 'ordered_list';

    public function toHtml(Node $node, string $children): string
    {
        return "<ol>$children</ol>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'ol')
        ];
    }

}