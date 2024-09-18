<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Audio;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Audio extends NodeType
{
    public string $name = 'audio';
    public string $group = 'block';
    public string $attrs = AudioAttrs::class;

    public function toHtml(Node $node, $children): string
    {
        return '<audio controls src="' . $node->attr('src') . '"></audio>';
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'audio',
                getAttrs: function (\DOMElement $node) {
                    $src = $node->getAttribute('src');

                    if (!$src) {
                        return null;
                    }

                    return AudioAttrs::fromArray([
                        'src' => $src
                    ]);
                },
            )
        ];
    }
}