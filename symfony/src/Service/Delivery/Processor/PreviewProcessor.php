<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\TemplateRenderer\TemplateRendererService;
use App\Service\Delivery\TemplateRenderer\TemplateRenderingException;
use App\Service\Delivery\TemplateRenderer\TemplateRenderingPageNotFoundException;
use App\Service\Language\LanguageService;
use App\Service\Post\PostService;
use App\Service\Route\RouteService;

class PreviewProcessor
{

    public function __construct(
        private PostService $postService,
        private LanguageService $languageService,
        private TemplateRendererService $templateRendererService,
        private RouteService $routeService
    ) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $previewId = $matchedRoute->param('id');

        if (!$previewId) {
            return null;
        }

        $id = $this->postService->parsePreviewId($previewId);

        if (!$id) {
            return null;
        }

        $languageCode = $matchedRoute->param('lang');

        if (!$languageCode) {
            return null;
        }

        $language = $this->languageService->getLanguageByCode($blog, $languageCode);

        if (!$language) {
            return null;
        }

        $post = $this->postService->getPostById($id);

        if (!$post) {
            return null;
        }

        if ($post->getblog()->getId() !== $blog->getId()) {
            return null;
        }

        $route = $this->routeService->getRouteByName($blog, $post->isPage() ? 'page' : 'post');
        assert($route !== null);

        try {
            $rendered = $this->templateRendererService->renderForRoute(
                $blog,
                $language,
                $route,
                $matchedRoute,
                presetModel: $post
            );

            return DeliveryResponse::forFile(
                DeliveryFileType::TEMPLATE,
                $rendered,
                cache: false
            );
        } catch (TemplateRenderingException $e) {
            return DeliveryResponse::forError($e->getMessage());
        } catch (TemplateRenderingPageNotFoundException) {
            return null;
        }
    }
}
