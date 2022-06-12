<?php

namespace App\Domains\Delivery\Processors;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\PostPreviewSecretEncryptor;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Delivery\TemplateRenderer\TemplateRenderer;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostRepository;

class PreviewProcessor implements RouteProcessorInterface
{

    private ?DeliveryAPIResponseObject $responseObject = null;

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {

        $id = PostPreviewSecretEncryptor::decryptPreviewSecret($matchedRoute->param('id'));

        if (!$id)
            return;

        $languageCode = $matchedRoute->param('lang');
        $language = LanguageRepository::getLanguageByCode($pathMatcher->blog, $languageCode);

        if (!$language)
            return;

        $pathMatcher->setCustomLanguage($language);

        $post = PostRepository::getPostById($id);

        $blog = $pathMatcher->blog;
        $routes = $blog->routes;

        /**
         * Fake-update the route
         */
        $route = $routes->firstWhere('name', $post->is_page ? 'page' : 'post');
        $matchedRoute->route = $route;

        $templateRenderer = new TemplateRenderer(
            $pathMatcher,
            $matchedRoute,
            null
        );
        $templateRenderer->setModel($post);

        $this->responseObject = $templateRenderer->getResponseObject();
    }

    public function getResponseObject(): ?DeliveryAPIResponseObject
    {
        return $this->responseObject;
    }
}
