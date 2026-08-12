<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class OrderedList extends NodeType
{
    public string $name = 'ordered_list';
    public string $group = 'block';
    public ?string $content = 'list_item*';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'ol'),
        ];
    }
}
