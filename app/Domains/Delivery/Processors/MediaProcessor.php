<?php declare(strict_types=1);

namespace App\Domains\Delivery\Processors;

use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Media\MediaRepository;
use App\Helpers\MimeTypes;
use Intervention\Image\Facades\Image;

/**
 * @phpstan-type ContentAndMimeType array{content: string, mimeType: string}
 */
class MediaProcessor extends RouteProcessorAbstract
{
    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {
        $fileName = $matchedRoute->param('file_name');

        /**
         * Supports additional parameters like: resizing images
         */
        $additional = $matchedRoute->param('additional');

        $media = MediaRepository::getByBlogIdAndName($pathMatcher->blog->id, $fileName);

        if (!$media) {
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

        // JPG and PNG -> WebP
        [
            'content' => $content,
            'mimeType' => $mimeType
        ] = $this->convertImage($content, $mimeType);

        if ($additional) {
            $addedAdditional = $this->addAdditional(
                $content,
                $mimeType,
                $additional
            );

            if (!$addedAdditional)
                return;

            $content = $addedAdditional['content'];
            $mimeType = $addedAdditional['mimeType'];
        }

        $this->setResponseObject(DeliveryAPIResponseObject::forFile(
            DeliveryAPIFileTypeEnum::MEDIA,
            $content,
            $mimeType,
            browserCache: DeliveryAPICacheControlHeaderEnum::CACHE_ONE_YEAR
        ));
    }


    /**
     * @return ContentAndMimeType|null
     */
    private function addAdditional(string $content, string $mimeType, string $additional) : ?array
    {

        if (
            $mimeType === 'image/webp' &&
            preg_match('/^(\d+)w$/', $additional, $matches)
        ) {

            $width = (int) $matches[1];

            $image = Image::make($content)->widen($width, function ($constraint) {
                $constraint->upsize();
            })->encode('webp', 100);

            return [
                'content' => (string) $image,
                'mimeType' => $mimeType,
            ];

        }

        return null;

    }

    /**
     * @return ContentAndMimeType
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
            'content' => $content,
            'mimeType' => $mimeType
        ];

    }

}
