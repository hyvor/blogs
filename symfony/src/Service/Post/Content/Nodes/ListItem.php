<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class ListItem extends NodeType
{
    public string $name = 'list_item';
    public ?string $content = 'block*';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'li'),
        ];
    }
}
