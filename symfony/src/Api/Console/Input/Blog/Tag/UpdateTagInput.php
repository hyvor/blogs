<?php

namespace App\Api\Console\Input\Blog\Tag;

class UpdateTagInput
{
    public ?bool $is_private = null;

    public ?string $slug = null;

    public ?string $code_head = null;

    public ?string $code_foot = null;
}
