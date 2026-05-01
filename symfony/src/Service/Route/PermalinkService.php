<?php

namespace App\Service\Route;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Service\AppConfig;

class PermalinkService
{
    public function __construct(private AppConfig $appConfig) {}

    public function getBlogUrl(Blog $blog): string
    {
        return match ($blog->getHostingAt()) {
            BlogHostingAt::SUBDOMAIN => $this->buildSubdomainUrl($blog),
            BlogHostingAt::DOMAIN => 'https://' . $blog->getHostingDomain(),
            BlogHostingAt::SELF => $blog->getHostingUrl() ?? '',
        };
    }

    private function buildSubdomainUrl(Blog $blog): string
    {
        $url = $this->appConfig->getDeliveryUrl();
        $scheme = parse_url($url, PHP_URL_SCHEME) ?? 'https';
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $port = parse_url($url, PHP_URL_PORT);
        $portStr = $port ? ":$port" : '';
        return "$scheme://{$blog->getSubdomain()}.$host$portStr";
    }
}
