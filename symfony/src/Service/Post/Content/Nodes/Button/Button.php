<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Button;

use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Button extends NodeType
{
    public string $name = 'button';
    public ?string $content = 'text';
    public string $group = 'block';
    public string $attrs = ButtonAttrs::class;

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'a',
                getAttrs: function (DOMElement $node): ButtonAttrs|bool {
                    $class = $node->getAttribute('class');

                    if (!$class || !str_contains($class, 'button')) {
                        return false;
                    }

                    $href = $node->getAttribute('href');
                    $text = $node->textContent;

                    if (!$href || !$text) {
                        return false;
                    }

                    return ButtonAttrs::fromArray(['href' => $href]);
                }
            ),
        ];
    }
}
