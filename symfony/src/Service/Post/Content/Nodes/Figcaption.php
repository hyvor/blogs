<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Figcaption extends NodeType
{
    public string $name = 'figcaption';
    public ?string $content = 'inline*';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'figcaption'),
        ];
    }
}
