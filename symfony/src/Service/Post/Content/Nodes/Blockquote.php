<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Blockquote extends NodeType
{
    public string $name = 'blockquote';
    public string $attrs = BlockquoteAttrs::class;
    public ?string $content = 'block+';
    public string $group = 'block';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'blockquote'),
        ];
    }
}
