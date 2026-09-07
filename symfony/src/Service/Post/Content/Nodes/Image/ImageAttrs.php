<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Image;

use App\Service\Post\Content\Nodes\SuggestionsAttrTrait;
use Hyvor\Phrosemirror\Types\AttrsType;

class ImageAttrs extends AttrsType
{
    use SuggestionsAttrTrait;

    public ?string $src;
    public ?string $alt = null;
    public null|int|string $width = null;
    public null|int|string $height = null;
}
