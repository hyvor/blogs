<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Callout;

use Hyvor\Phrosemirror\Types\AttrsType;

class CalloutAttrs extends AttrsType
{
    public ?string $emoji;
    public ?string $bg;
    public ?string $fg;
}