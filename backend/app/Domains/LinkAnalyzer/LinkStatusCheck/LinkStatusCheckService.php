<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;

class LinkStatusCheckService
{

    public function __construct(
        private InternalStatusCheck $internalStatusCheck,
        private ExternalStatusCheck $externalStatusCheck
    ) {
    }

    /**
     * @param string[] $urls Absolute HTTP/HTTPS URLs to check
     * @param Blog|null $blog set this to analyze some URLs as internal links
     * @return array<string, StatusResult> URLs as keys and HTTP status codes as values
     */
    public function check(
        array $urls,
        ?Blog $blog = null
    ): array {
        $urls = array_unique($urls);

        [$internalUrls, $externalUrls] = $this->separateInternalAndExternalUrls($urls, $blog);

        $internalStatus = [];
        if ($blog) {
            $this->internalStatusCheck->setBlog($blog);
            $internalStatus = $this->internalStatusCheck->check($internalUrls);
        }
        $externalStatus = $this->externalStatusCheck->check($externalUrls);

        return array_merge($internalStatus, $externalStatus);
    }

    /**
     * @param string[] $urls
     * @return array{0: string[], 1: string[]}
     */
    private function separateInternalAndExternalUrls(array $urls, ?Blog $blog): array
    {
        if (!$blog) {
            return [[], $urls];
        }

        $internalUrls = [];
        $externalUrls = [];

        $baseUrl = PermalinkRepository::getBaseUrl($blog);

        foreach ($urls as $url) {
            if (str_starts_with($url, $baseUrl)) {
                $internalUrls[] = $url;
            } else {
                $externalUrls[] = $url;
            }
        }

        return [$internalUrls, $externalUrls];
    }

}