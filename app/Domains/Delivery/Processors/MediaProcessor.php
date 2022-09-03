<?php

namespace App\Domains\Delivery\Processors;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Media\MediaRepository;
use App\Helpers\MimeTypes;

class MediaProcessor extends RouteProcessorAbstract
{
    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {
        $fileName = $matchedRoute->param('file_name');
        $media = MediaRepository::getByBlogIdAndName($pathMatcher->blog->id, $fileName);

        if (! $media) {
            return;
        }

        $content = MediaRepository::getContents($media);

        if (!$content)
            return;

        $mimeType = $media->extension ? MimeTypes::getMimeFromExtension($media->extension) : 'image/png';

        $this->setResponseObject(DeliveryAPIResponseObject::forFile(
            DeliveryAPIFileTypeEnum::MEDIA,
            $content,
            $mimeType
        ));
    }
}
