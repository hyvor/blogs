<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Table\TableCell;

class TableCell extends TableCellBase
{

    public string $name = 'table_cell';

    public function getTag(): string
    {
        return 'td';
    }

}