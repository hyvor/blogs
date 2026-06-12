<?php

namespace App\Api\Data\Object;

use App\Entity\Route;
use App\Service\Delivery\RouteMatcher\MatchedRoute;

class RouteObject
{
    public string $name;
    public string $template;
    public ?string $posts_filter;
    public ?string $content_type;
    /** @var array<mixed> */
    public array $params;

    public function __construct(Route $route, MatchedRoute $matchedRoute, string $currentTemplateName)
    {
        $this->name = $matchedRoute->name;
        $this->params = $matchedRoute->params;
        $this->template = str_replace('.twig', '', $currentTemplateName);
        $this->posts_filter = $route->getPostsFilter();
        $this->content_type = $route->getContentType();
    }
}
