<?php

namespace App\Service\Delivery\Processor;

use App\Api\Data\Factory\BlogObjectFactory;
use App\Entity\Blog;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Language\LanguageService;

class RobotsTxtProcessor
{
    public function __construct(
        private LanguageService $languageService,
        private TwigRendererService $twigRenderer,
        private BlogObjectFactory $blogObjectFactory
    ) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $robots = $blog->getMeta()->seo_robots_txt;
        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);

        try {
            $rendered = $this->twigRenderer->renderString($robots, [
                '_blog' => $this->blogObjectFactory->create($blog, $primaryLanguage)
            ]);
        } catch (\Exception) {
            $rendered = $robots;
        }

        return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $rendered, 'text/plain');
    }
}
