<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes;

use Hyvor\Phrosemirror\Types\AttrsType;

class OrderedListAttrs extends AttrsType
{
    use SuggestionsAttrTrait;
}
