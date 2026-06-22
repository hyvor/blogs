<?php

namespace App\Api\Console\Object;

use App\Entity\Route;

class RouteObject
{
    public int $id;
    public string $name;
    public string $match;
    public string $template;
    public ?string $posts_filter;
    public ?string $content_type;
    public bool $is_enabled;

    public function __construct(Route $route)
    {
        $this->id = $route->getId();
        $this->name = $route->getName();
        $this->match = $route->getMatch();
        $this->template = $route->getTemplate();
        $this->posts_filter = $route->getPostsFilter();
        $this->content_type = $route->getContentType();
        $this->is_enabled = $route->isEnabled();
    }
}
