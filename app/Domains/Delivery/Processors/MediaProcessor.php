<?php declare(strict_types=1);

namespace App\Domains\Delivery\Processors;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Media\MediaRepository;
use App\Helpers\MimeTypes;
use Intervention\Image\Facades\Image;

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

        $mimeType = $media->extension ?
            MimeTypes::getMimeFromExtension($media->extension) :
            MimeTypes::getMimeFromFileName($media->name);

        // https://stackoverflow.com/questions/1176022/unknown-file-type-mime
        $mimeType ??= 'application/octet-stream';

        [$content, $mimeType] = $this->convertImage($content, $mimeType);

        $this->setResponseObject(DeliveryAPIResponseObject::forFile(
            DeliveryAPIFileTypeEnum::MEDIA,
            $content,
            $mimeType
        ));
    }

    /**
     * @return array<string>
     */
    private function convertImage(string $content, string $mimeType) : array
    {

        if (
            $mimeType === 'image/png' ||
            $mimeType === 'image/jpeg'
        ) {
            $content = (string) Image::make($content)->encode('webp', 100);
            $mimeType = 'image/webp';
        }

        return [
            $content,
            $mimeType
        ];

    }

}
