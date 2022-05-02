<?php
namespace App\Domains\Delivery\Processors;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Delivery\TemplateRenderer\TemplateRenderer;
use App\Domains\Post\PostRepository;
use Illuminate\Contracts\Encryption\DecryptException;

class PreviewProcessor {

    private ?DeliveryAPIResponseObject $responseObject = null;

    /**
     * TODO: Add expiring
     */
    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute) {

        try {
            $id = decrypt($matchedRoute->param('id'));
        } catch (DecryptException) {
            return;
        }

        $post = PostRepository::getPostById($id);

        $blog = $pathMatcher->blog;
        $routes = $blog->routes;


        /**
         * Fake-update the route
         */
        $route = $routes->firstWhere('name', $post->is_page ? 'page' : 'post');
        $matchedRoute->route = $route;

        $templateRenderer = new TemplateRenderer(
            $blog,
            $matchedRoute,
            $post->language,
            null
        );
        $templateRenderer->setModel($post);

        $this->responseObject = $templateRenderer->getResponseObject();
    }

    public function getResponseObject() : ?DeliveryAPIResponseObject {
        return $this->responseObject;
    }

}