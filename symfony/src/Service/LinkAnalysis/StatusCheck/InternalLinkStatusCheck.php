<?php

namespace App\Service\LinkAnalysis\StatusCheck;

use App\Entity\Blog;
use App\Entity\Enum\LinkAnalyzerCheckType;
use App\Service\Delivery\DeliveryService;
use App\Service\LinkAnalysis\Dto\StatusResult;
use App\Service\Route\PermalinkService;

class InternalLinkStatusCheck implements LinkStatusCheckInterface
{
    private Blog $blog;

    public function __construct(
        private DeliveryService $deliveryService,
        private PermalinkService $permalinkService,
    ) {}

    public function setBlog(Blog $blog): void
    {
        $this->blog = $blog;
    }

    /**
     * @param string[] $urls
     * @return array<string, StatusResult>
     */
    public function check(array $urls): array
    {
        $baseUrl = $this->permalinkService->getBlogUrl($this->blog);
        $statuses = [];

        foreach ($urls as $url) {
            // this should be guaranteed by the caller
            assert(str_starts_with($url, $baseUrl));

            $path = substr($url, strlen($baseUrl));
            $parsedPath = parse_url($path, PHP_URL_PATH); // remove query and fragment
            $path = is_string($parsedPath) ? $parsedPath : '/';

            $response = $this->deliveryService->getResponse($this->blog, $path);
            $statuses[$url] = new StatusResult(
                LinkAnalyzerCheckType::INTERNAL,
                $response->status
            );

            usleep(100); // sleep for 100 microseconds to prevent database overload
        }

        return $statuses;
    }
}
