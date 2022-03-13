<?php
namespace App\Domains\Delivery\RouteMatcher;

class MatchedRoute {

    // matched route name
    public string $name;

    // params key=>value
    public array $params;

    /**
     * $props from Symfony\Component\Routing\Matcher\UrlMatcher::match
     */
    public function __construct($props) {
        foreach ($props as $key => $value) {
            if ($key === '_route') {
                $this->name = $key;
            } else {
                $this->params[$key] = $value;
            }
        }
    }

    public function param($key) {
        return $params[$key] ?? null;
    }

}