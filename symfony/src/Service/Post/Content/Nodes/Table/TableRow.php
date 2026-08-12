<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Table;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

class TableRow extends NodeType
{
    public string $name = 'table_row';
    public ?string $content = '(table_cell | table_header)*';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'tr'),
        ];
    }
}
