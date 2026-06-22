<?php

namespace App\Domains\Post\Content\Nodes;

use Hyvor\Phrosemirror\Types\AttrsType;

class ButtonAttrs extends AttrsType
{

    public ?string $href = null;

}