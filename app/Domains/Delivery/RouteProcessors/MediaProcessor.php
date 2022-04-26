<?php
namespace App\Domains\Delivery\RouteProcessors;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Media\MediaRepository;
use App\Helpers\MimeTypes;

class MediaProcessor implements RouteProcessorInterface {

    private ?DeliveryAPIResponseObject $responseObject = null;

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute) {

        $fileName = $matchedRoute->param('file_name');
        $media = MediaRepository::getByBlogIdAndName($pathMatcher->blog->id, $fileName);

        if (!$media) {
            return;
        }

        $content = MediaRepository::getContents($media);
        $mimeType = MimeTypes::getMimeFromExtension($media->extension);

        $this->responseObject = DeliveryAPIResponseObject::forFile($content, $mimeType);

    }

    public function getResponseObject() : ?DeliveryAPIResponseObject {
        return $this->responseObject;
    }

}