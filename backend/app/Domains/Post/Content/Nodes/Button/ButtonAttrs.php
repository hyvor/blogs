<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Button;

use Hyvor\Phrosemirror\Types\AttrsType;

class ButtonAttrs extends AttrsType
{
    public ?string $href;
    public ?string $size;
    public ?string $align;
    public ?string $bg;
    public ?string $fg;
}