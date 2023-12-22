<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Table\TableCell;

class TableHeader extends TableCellBase
{
    public string $name = 'table_header';

    public function getTag(): string
    {
        return 'th';
    }
}