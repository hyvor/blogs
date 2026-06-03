<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
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
        $metaRobots = $meta['seo_robots_txt'] ?? null;
        $robots = is_string($metaRobots) ? $metaRobots : self::DEFAULT_ROBOTS_TXT;

        try {
            $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        } catch (\Exception) {
            return null;
        }

        $blogUrl = $this->permalinkService->getBlogPermalink($blog, $primaryLanguage);

        try {
            $template = $this->twig->createTemplate($robots);
            $rendered = $template->render(['_blog' => ['base_url' => $blogUrl]]);
        } catch (\Exception) {
            $rendered = $robots;
        }

        return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $rendered, 'text/plain');
    }
}
