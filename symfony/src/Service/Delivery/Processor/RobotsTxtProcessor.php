<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Language\LanguageService;
use App\Service\Route\PermalinkService;

class RobotsTxtProcessor
{
    public function __construct(
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private TwigRendererService $twigRenderer,
    ) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $robots = $blog->getMeta()->seo_robots_txt;

        try {
            $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        } catch (\Exception) {
            return null;
        }

        $blogUrl = $this->permalinkService->getBlogPermalink($blog, $primaryLanguage);

        try {
            $rendered = $this->twigRenderer->renderString($robots, [
                '_blog' => ['base_url' => $blogUrl],
            ]);
        } catch (\Exception) {
            $rendered = $robots;
        }

        return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $rendered, 'text/plain');
    }
}
