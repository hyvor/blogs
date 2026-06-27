<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Heading;

use Hyvor\Phrosemirror\Types\AttrsType;

class HeadingAttrs extends AttrsType
{
    public int $level = 2;
    public ?string $id = null;
}
