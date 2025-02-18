<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

use App\Domains\Delivery\DeliveryService;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;

class InternalStatusCheck implements LinkStatusCheckInterface
{
    private Blog $blog;

    public function setBlog(Blog $blog): void
    {
        $this->blog = $blog;
    }

    public function check(array $urls): array
    {
        $blogBasePath = PermalinkRepository::getBaseUrl($this->blog);

        $statuses = [];

        foreach ($urls as $url) {
            // this should be guaranteed by the caller
            assert(str_starts_with($url, $blogBasePath));

            $restPath = substr($url, strlen($blogBasePath));
            $deliveryObject = DeliveryService::getResponseObject($this->blog, $restPath);
            $statuses[$url] = new StatusResult(
                type: StatusCheckType::INTERNAL,
                httpStatus: $deliveryObject->status,
            );

            usleep(100); // sleep for 100 microseconds to prevent database overload
        }

        return $statuses;
    }

}