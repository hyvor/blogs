<?php

namespace App\Api\Console\Input\Media;

class GetMediaInput
{
    public int $limit = 50;

    public int $offset = 0;

    public ?string $search = null;

    /** @var string[]|null */
    public ?array $extensions = null;

    public ?string $type = null;
}
