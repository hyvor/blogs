<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Embed;

use Hyvor\Phrosemirror\Types\AttrsType;

class EmbedAttrs extends AttrsType
{
    public ?string $url;
}
