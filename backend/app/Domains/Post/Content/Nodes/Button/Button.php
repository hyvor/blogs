<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Button;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Button extends NodeType
{
    public string $name = 'button';
    public string $group = 'block';
    public string $attrs = ButtonAttrs::class;

    public function toHtml(Node $node, $children): string
    {
        $href = $node->attr('href');
        return "<div class=\"button-wrap\"><a href=$href>$children</a></div>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'button',
                getAttrs: function (\DOMElement $node) {
                    $href = $node->getAttribute('href');
                    $size = $node->getAttribute('size');
                    $align = $node->getAttribute('align');
                    $bg = $node->getAttribute('bg');
                    $fg = $node->getAttribute('fg');


                    return ButtonAttrs::fromArray([
                        'href' => $href,
                        'size' => $size,
                        'align' => $align,
                        'bg' => $bg,
                        'fg' => $fg
                    ]);
                },
            )
        ];
    }
}