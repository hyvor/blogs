<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Embed;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class EmbedAttrs extends AttrsType
{
    use SuggestionsAttrTrait;

    public ?string $url;
}
