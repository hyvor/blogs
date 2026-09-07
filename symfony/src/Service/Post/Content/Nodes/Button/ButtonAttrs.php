<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Button;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class ButtonAttrs extends AttrsType
{
    use SuggestionsAttrTrait;

    public ?string $href = null;
}
