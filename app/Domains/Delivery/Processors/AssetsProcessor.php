<?php

namespace App\Domains\Delivery\Processors;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Theme\ThemeFilesRepository;
use App\Helpers\MimeTypes;

class AssetsProcessor extends RouteProcessorAbstract
{
    private ?DeliveryAPIResponseObject $responseObject = null;

    private const DEAULT_ASSETS = [
        'flashload.js',
    ];

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {
        $fileName = $matchedRoute->param('file_name');
        $file = ThemeFilesRepository::getFile(
            $pathMatcher->blog,
            $fileName,
            ThemeFileFolderEnum::ASSETS
        );

        if ($file) {
            $content = $file->content;
        } elseif (in_array($fileName, self::DEAULT_ASSETS)) {
            $content = file_get_contents(resource_path("assets/$fileName"));
        } else {
            return;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeType = MimeTypes::getMimeFromExtension($extension);

        $this->setResponseObject(DeliveryAPIResponseObject::forFile(
            DeliveryAPIFileTypeEnum::ASSET,
            $content,
            $mimeType
        ));
    }

}
