<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Audio;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class AudioAttrs extends AttrsType
{
    use SuggestionsAttrTrait;

    public ?string $src;
}
