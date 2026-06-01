<?php

namespace App\Api\Console\Input\Blog\Route;

class UpdateRouteInput
{
    public string $name;

    public string $match;

    public string $template;

    public ?string $posts_filter;

    public ?string $content_type;
}
