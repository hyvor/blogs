<?php
namespace App\Domains\Delivery\RouteMatcher;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * This is mostly a wrapper around Symfony wrapper
 * 
 * 
 * https://symfony.com/doc/current/create_framework/routing.html
 */
class RouteMatcher {

    public RouteCollection $collection;
    public string $path;

    public function __construct($path) 
    {
        $this->collection = new RouteCollection();
        $this->path = $path;
    }

    public function add(string $routeName, string $match, array $defaults = [], array $requirements = []) 
    {
        $route = new Route($match, $defaults, $requirements);
        $this->collection->add($routeName, $route);
    }

    public function match() : ?MatchedRoute
    {
        $context = new RequestContext();
        $urlMatcher = new UrlMatcher($this->collection, $context);

        try {
            $props = $urlMatcher->match($this->path);
            $return = new MatchedRoute($props);
        } catch (ResourceNotFoundException) {
            $return = null;
        }

        return $return;
    }

}