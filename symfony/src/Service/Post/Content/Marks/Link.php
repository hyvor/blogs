<?php declare(strict_types=1);

namespace App\Service\Post\Content\Marks;

use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\MarkType;

class Link extends MarkType
{
    public string $name = 'link';
    public string $attrs = LinkAttrs::class;

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'a',
                getAttrs: function (DOMElement $node): LinkAttrs|bool {
                    $href = $node->getAttribute('href');

                    if (!$href) {
                        return false;
                    }

                    return LinkAttrs::fromArray(['href' => $href]);
                }
            ),
        ];
    }
}
