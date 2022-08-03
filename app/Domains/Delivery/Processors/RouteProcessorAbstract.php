<?php

namespace App\Domains\Delivery\Processors;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;

abstract class RouteProcessorAbstract
{
    private ?DeliveryAPIResponseObject $responseObject = null;

    abstract public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute);

    protected function setResponseObject(DeliveryAPIResponseObject $responseObject)
    {
        $this->responseObject = $responseObject;
    }

    public function getResponseObject(): ?DeliveryAPIResponseObject
    {
        return $this->responseObject;
    }
}
