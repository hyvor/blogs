<?php

namespace App\Domains\Delivery\Processors;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DataAPI\BlogObject;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Delivery\Twig\TwigRenderer;

class RobotsTxtProcessor extends RouteProcessorAbstract
{

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {

        $blog = $pathMatcher->blog;
        $robots = $blog->getMeta('seo_robots_txt') ?? '';

        $rendered = TwigRenderer::renderString($robots, [
            '_blog' => new BlogObject($blog, $blog->languages[0])
        ]);

        $this->setResponseObject(DeliveryAPIResponseObject::forFile(
            DeliveryAPIFileTypeEnum::TEMPLATE,
            $rendered,
            'text/plain'
        ));

    }
}