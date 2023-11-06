<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Audio;

use Hyvor\Phrosemirror\Types\AttrsType;

class AudioAttrs extends AttrsType
{
    public ?string $src;
}