<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Marks;

use Hyvor\Phrosemirror\Types\AttrsType;

class LinkAttrs extends AttrsType
{
    public string $href;
}