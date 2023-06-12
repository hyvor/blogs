<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Blockquote extends NodeType
{

    public string $name = 'blockquote';

    public function toHtml(Node $node, string $children): string
    {
        return "<blockquote>$children</blockquote>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'blockquote')
        ];
    }

}