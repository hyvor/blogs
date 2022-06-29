<?php

namespace App\Domains\Delivery\RouteMatcher;

use App\Models\Route;

class MatchedRoute
{
    // matched route name
    public string $name;

    public ?Route $route;

    // params key=>value
    public array $params = [];

    /**
     * $props from Symfony\Component\Routing\Matcher\UrlMatcher::match
     * $route is null for special routes
     */
    public function __construct($props, ?Route $route)
    {
        $this->route = $route;

        foreach ($props as $key => $value) {
            if ($key === '_route') {
                $this->name = $value;
            } else {
                $this->params[$key] = $value;
            }
        }
    }

    public function param($key)
    {
        return $this->params[$key] ?? null;
    }
}
