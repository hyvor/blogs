<?php declare(strict_types=1);

namespace App\Service\Post\Content\Marks;

use Hyvor\Phrosemirror\Types\AttrsType;

class SuggestionAttrs extends AttrsType
{
    public string $type = 'insert';
    public string $id = '';

    /** @var string[] */
    public array $add = [];

    /** @var array<int, array{type: string, attrs: array<string, mixed>}> */
    public array $remove = [];
}
