<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Service\Delivery\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;

class PreviewProcessor
{
    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        // TODO: implement once TemplateRenderer is migrated
        return null;
    }
}
