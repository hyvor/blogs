<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class BulletList extends NodeType
{

    public string $name = 'bullet_list';

    public function toHtml(Node $node, string $children): string
    {
        return "<ul>$children</ul>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'ul')
        ];
    }

}