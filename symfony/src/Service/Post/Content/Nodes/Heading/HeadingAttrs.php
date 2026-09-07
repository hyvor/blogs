<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Heading;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class HeadingAttrs extends AttrsType
{
    use SuggestionsAttrTrait;

    public int $level = 2;
    public ?string $id = null;
}
