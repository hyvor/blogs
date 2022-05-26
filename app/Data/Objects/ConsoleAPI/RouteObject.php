<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\Route;

class RouteObject
{
    public int $id;
    public int $created_at;
    public string $name;
    public string $match;
    public string $template;
    public ?string $posts_filter;
    public ?string $content_type;
    public bool $is_enabled;

    public function __construct(Route $route)
    {

        $this->id = $route->id;
        $this->created_at = $route->created_at->timestamp;
        $this->name = $route->name;
        $this->match = $route->match;
        $this->template = $route->template;
        $this->posts_filter = $route->posts_filter;
        $this->content_type = $route->content_type;
        $this->is_enabled = (bool) $route->is_enabled;

    }
}
