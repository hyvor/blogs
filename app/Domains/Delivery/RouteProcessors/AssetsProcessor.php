<?php
namespace App\Domains\Delivery\RouteProcessors;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\ThemeFiles\ThemeFilesRepository;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Helpers\MimeTypes;

class AssetsProcessor implements RouteProcessorInterface {

    private ?DeliveryAPIResponseObject $responseObject = null;

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute) {

        $fileName = $matchedRoute->param('file_name');
        $file = ThemeFilesRepository::getFile(
            $pathMatcher->getThemable(), 
            $fileName, 
            ThemeFileFolderEnum::ASSETS
        );

        if (!$file) {
            return;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeType = MimeTypes::getMimeFromExtension($extension);

        $this->responseObject = DeliveryAPIResponseObject::forFile($file->content, $mimeType);

    }

    public function getResponseObject() : ?DeliveryAPIResponseObject {
        return $this->responseObject;
    }

}
