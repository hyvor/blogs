<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Bookmark;

use Hyvor\Phrosemirror\Types\AttrsType;

class BookmarkAttrs extends AttrsType
{
    public ?string $url;
}
