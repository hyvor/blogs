<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Button;

use Hyvor\Phrosemirror\Types\AttrsType;

class ButtonAttrs extends AttrsType
{
    public ?string $href = null;
}
