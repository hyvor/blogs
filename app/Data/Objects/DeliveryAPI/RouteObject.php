<?php

namespace App\Data\Objects\DeliveryAPI;

use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use Exception;

// a combination of Model\Route and MatchedRoute
class RouteObject
{
    public string $name;

    public string $template;

    public ?string $posts_filter;

    public ?string $content_type;

    public array $params;

    public function __construct(MatchedRoute $matchedRoute, string $currentTemplateName)
    {
        $this->name = $matchedRoute->name;
        $this->params = $matchedRoute->params;

        if (! $matchedRoute->route) {
            /**
             * RouteObject is called from the TemplateRenderer
             * Therefore,
             */
            throw new Exception('Route is null');
        }

        $route = $matchedRoute->route;

        $this->template = str_replace('.twig', '', $currentTemplateName);
        $this->posts_filter = $route->posts_filter;
        $this->content_type = $route->content_type;
    }
}
