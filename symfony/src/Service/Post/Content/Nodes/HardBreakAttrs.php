<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Types\AttrsType;

class HardBreakAttrs extends AttrsType
{
    use SuggestionsAttrTrait;
}
