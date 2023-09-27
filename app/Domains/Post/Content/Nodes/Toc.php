<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Toc extends NodeType
{
    public string $name = 'paragraph';
    public ?string $content = 'inline*';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        return "";
    }

    public function fromHtml(): array
    {
        return [
        ];
    }

}