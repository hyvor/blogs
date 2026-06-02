<?php

namespace App\Service\Delivery\RouteMatcher;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteMatcher
{
    private RouteCollection $collection;

    public function __construct(private string $path)
    {
        $this->collection = new RouteCollection();
    }

    /**
     * @param array<string, ?string> $defaults
     * @param array<string, string> $requirements
     */
    public function add(
        string $routeName,
        string $match,
        array $defaults = [],
        array $requirements = [],
    ): void {
        $this->collection->add($routeName, new Route($match, $defaults, $requirements));
    }

    public function match(): ?MatchedRoute
    {
        $matcher = new UrlMatcher($this->collection, new RequestContext());
        try {
            return new MatchedRoute($matcher->match($this->path));
        } catch (ResourceNotFoundException) {
            return null;
        }
    }
}
