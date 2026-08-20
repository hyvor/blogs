<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Callout;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class CalloutAttrs extends AttrsType
{
    use SuggestionsAttrTrait;

    public ?string $emoji;
    public ?string $bg;
    public ?string $fg;
}
