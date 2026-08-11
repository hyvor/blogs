<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Embed;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Embed extends NodeType
{
    public string $name = 'embed';
    public string $attrs = EmbedAttrs::class;

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'x-embed',
                getAttrs: function ($el) {
                    $url = $el->getAttribute('data-url');

                    if (!$url) {
                        return false;
                    }

                    return EmbedAttrs::fromArray([
                        'url' => $url,
                    ]);
                }
            ),
        ];
    }
}
