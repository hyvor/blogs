<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Figure extends NodeType
{
    public string $name = 'figure';
    public string $attrs = FigureAttrs::class;
    public ?string $content = '(image|embed) figcaption?';
    public string $group = 'block';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'figure'),
        ];
    }
}
