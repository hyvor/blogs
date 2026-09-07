<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Heading;

use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Heading extends NodeType
{
    public const ALLOWED_LEVELS = [1, 2, 3, 4, 5, 6];

    public string $name = 'heading';
    public string $attrs = HeadingAttrs::class;
    public ?string $content = 'inline*';
    public string $group = 'block';

    public function fromHtml(): array
    {
        return array_map(function (int $level) {
            return new ParserRule(
                tag: "h{$level}",
                getAttrs: function (DOMElement $node) use ($level) {
                    return HeadingAttrs::fromArray([
                        'level' => $level,
                        'id' => $node->getAttribute('id'),
                    ]);
                },
            );
        }, self::ALLOWED_LEVELS);
    }
}
