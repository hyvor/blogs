<?php

namespace App\Domains\Delivery\Processors;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;

interface RouteProcessorInterface
{
    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute);
    public function getResponseObject(): ?DeliveryAPIResponseObject;
}
