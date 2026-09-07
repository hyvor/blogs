<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Audio;

use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Audio extends NodeType
{
    public string $name = 'audio';
    public string $group = 'block';
    public string $attrs = AudioAttrs::class;

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'audio',
                getAttrs: function (DOMElement $node) {
                    $src = $node->getAttribute('src');

                    if (!$src) {
                        return null;
                    }

                    return AudioAttrs::fromArray([
                        'src' => $src,
                    ]);
                },
            ),
        ];
    }
}
