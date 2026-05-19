<?php

namespace App\Api\Console\Input\Blog\Route;

class UpdateRouteInput
{
    public ?string $name = null;

    public ?string $match = null;

    public ?string $template = null;

    public ?string $posts_filter = null;

    public ?string $content_type = null;
}
