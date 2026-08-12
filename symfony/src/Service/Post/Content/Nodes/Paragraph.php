<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Paragraph extends NodeType
{
    public string $name = 'paragraph';
    public ?string $content = 'inline*';
    public string $group = 'block';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'p'),
        ];
    }
}
