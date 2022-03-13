<?php
namespace App\Domains\Delivery\RouteProcessors;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use Illuminate\Contracts\Encryption\DecryptException;

class PreviewProcessor {

    private ?DeliveryAPIResponseObject $responseObject = null;

    /**
     * TODO: Add expiring
     */
    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute) {

        //TODO:
        /* try {
            $id = decrypt($matchedRoute->param('id'));
        } catch (DecryptException) {
            return;
        }

        $post = PostRepository::getPostById($id);

        $html = BlogThemeTemplateRepository::renderFile(
            $blog,
            'post',
            $currentLang,
            DeliveryAPIScopeEnum::POST,
            $post
        );
        
        return DeliveryAPIResponseObject::forFile($html);

        $this->responseObject = DeliveryAPIResponseObject::forFile($css, 'text/css'); */

    }

    public function getResponseObject() : ?DeliveryAPIResponseObject {
        return $this->responseObject;
    }

}