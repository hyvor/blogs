<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Table;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class Table extends NodeType
{
    public string $name = 'table';
    public ?string $content = 'table_row+';
    public string $group = 'block';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'table'),
        ];
    }
}
