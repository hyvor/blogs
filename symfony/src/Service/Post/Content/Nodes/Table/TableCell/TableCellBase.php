<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Table\TableCell;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;

abstract class TableCellBase extends NodeType
{
    public ?string $content = 'block+';
    public string $attrs = TableCellAttrs::class;

    abstract public function getTag(): string;

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: $this->getTag()),
        ];
    }
}
