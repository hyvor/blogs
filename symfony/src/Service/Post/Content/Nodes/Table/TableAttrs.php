<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Table;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class TableAttrs extends AttrsType
{
    use SuggestionsAttrTrait;
}
