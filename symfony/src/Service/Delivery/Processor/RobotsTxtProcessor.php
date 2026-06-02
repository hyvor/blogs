<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Service\Delivery\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Language\LanguageService;
use App\Service\Route\PermalinkService;
use Twig\Environment;

class RobotsTxtProcessor
{
    private const DEFAULT_ROBOTS_TXT = <<<'TEXT'
User-agent: *
Sitemap: {{ _blog.base_url }}/sitemap.xml
Disallow: /p/
TEXT;

    public function __construct(
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private Environment $twig,
    ) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $meta = $blog->getMeta() ?? [];
        $robots = $meta['seo_robots_txt'] ?? self::DEFAULT_ROBOTS_TXT;

        try {
            $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        } catch (\Exception) {
            return null;
        }

        $blogUrl = $this->permalinkService->getBlogPermalink($blog, $primaryLanguage);

        try {
            $template = $this->twig->createTemplate((string)$robots);
            $rendered = $template->render([
                '_blog' => ['base_url' => $blogUrl],
            ]);
        } catch (\Exception) {
            $rendered = (string)$robots;
        }

        return DeliveryResponse::forFile($rendered, 'text/plain');
    }
}
