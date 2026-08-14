<?php

namespace App\Service\LinkAnalysis;

use App\Entity\Blog;
use App\Service\LinkAnalysis\Dto\StatusResult;
use App\Service\LinkAnalysis\StatusCheck\ExternalLinkStatusCheck;
use App\Service\LinkAnalysis\StatusCheck\InternalLinkStatusCheck;
use App\Service\Route\PermalinkService;

class LinkStatusChecker
{
    public function __construct(
        private InternalLinkStatusCheck $internalCheck,
        private ExternalLinkStatusCheck $externalCheck,
        private PermalinkService $permalinkService,
    ) {}

    /**
     * @param string[] $urls Absolute HTTP/HTTPS URLs to check
     * @return array<string, StatusResult> URL => StatusResult
     */
    public function check(array $urls, ?Blog $blog = null): array
    {
        $urls = array_unique($urls);

        [$internalUrls, $externalUrls] = $this->separateUrls($urls, $blog);

        $internalStatuses = [];
        if ($blog !== null && !empty($internalUrls)) {
            $this->internalCheck->setBlog($blog);
            $internalStatuses = $this->internalCheck->check($internalUrls);
        }
        $externalStatuses = $this->externalCheck->check($externalUrls);

        return array_merge($internalStatuses, $externalStatuses);
    }

    /**
     * @param string[] $urls
     * @return array{0: string[], 1: string[]}
     */
    private function separateUrls(array $urls, ?Blog $blog): array
    {
        if ($blog === null) {
            return [[], $urls];
        }

        $baseUrl = $this->permalinkService->getBlogUrl($blog);
        $internal = [];
        $external = [];

        foreach ($urls as $url) {
            if (str_starts_with($url, $baseUrl)) {
                $internal[] = $url;
            } else {
                $external[] = $url;
            }
        }

        return [$internal, $external];
    }
}
