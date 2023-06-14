<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Text extends NodeType
{
    public string $name = 'text';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: '#text')
        ];
    }

}