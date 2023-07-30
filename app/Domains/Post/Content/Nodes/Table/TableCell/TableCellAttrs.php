<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Table\TableCell;

use Hyvor\Phrosemirror\Types\AttrsType;

class TableCellAttrs extends AttrsType
{

    public int $colspan = 1;
    public int $rowspan = 1;

    /**
     * @var int[]|null
     */
    public ?array $colwidth = null;

}