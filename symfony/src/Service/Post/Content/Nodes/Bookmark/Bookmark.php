<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Bookmark;

use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Bookmark extends NodeType
{
    public string $name = 'bookmark';
    public string $attrs = BookmarkAttrs::class;
    public string $group = 'block';

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'a',
                getAttrs: function (DOMElement $node) {
                    if ($node->getAttribute('class') !== 'bookmark') {
                        return false;
                    }

                    if (!$node->getAttribute('data-url')) {
                        return false;
                    }

                    return BookmarkAttrs::fromArray([
                        'url' => $node->getAttribute('data-url'),
                    ]);
                }
            ),
        ];
    }
}
