<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Table\TableCell;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class TableCellAttrs extends AttrsType
{
    use SuggestionsAttrTrait;

    public int $colspan = 1;
    public int $rowspan = 1;

    /**
     * @var int[]|null
     */
    public ?array $colwidth = null;
}
