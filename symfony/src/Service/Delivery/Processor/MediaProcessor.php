<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Service\Delivery\Dto\CacheControl;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\MimeTypes;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Media\MediaService;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class MediaProcessor
{
    public function __construct(private MediaService $mediaService)
    {
    }

    /**
     * Path of a media file in the blog's delivery cache. Shared by the legacy blog-host route
     * ({blogUrl}/media/{file_name}/{additional}) and the media URL (see DeliveryService::getMediaResponse)
     */
    public static function getCachePath(string $fileName, ?string $additional = null): string
    {
        return '/media/' . $fileName . ($additional !== null ? '/' . $additional : '');
    }

    /**
     * Legacy route on the blog's host: {blogUrl}/media/{file_name}/{additional}
     */
    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $fileName = $matchedRoute->param('file_name');
        if ($fileName === null) {
            return null;
        }

        return $this->processFile($blog, $fileName, $matchedRoute->param('additional'));
    }

    /**
     * @param string|null $additional ex: "500w" to resize the image to 500px width
     */
    public function processFile(Blog $blog, string $fileName, ?string $additional = null): ?DeliveryResponse
    {
        $media = $this->mediaService->getMediaByBlogAndName($blog, $fileName);
        if ($media === null) {
            return null;
        }

        $content = $this->mediaService->getContentsStream($media);
        if ($content === null) {
            return null;
        }

        $mimeType = $media->getExtension()
            ? MimeTypes::getMimeFromExtension($media->getExtension())
            : MimeTypes::getMimeFromFileName($media->getName());

        // https://stackoverflow.com/questions/1176022/unknown-file-type-mime
        $mimeType ??= 'application/octet-stream';

        ['content' => $content, 'mimeType' => $mimeType] = $this->convertImage(
            $content,
            $mimeType,
            $this->getWidth($additional)
        );

        return DeliveryResponse::forFile(
            DeliveryFileType::MEDIA,
            $content,
            $mimeType,
            cacheControl: CacheControl::ONE_YEAR,
        );
    }

    private function getWidth(?string $additional): ?int
    {
        if ($additional === null) {
            return null;
        }

        if (preg_match('/^(\d+)w$/', $additional, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * @param resource $contentStream
     * @return array{content: string, mimeType: string}
     */
    private function convertImage(
        $contentStream,
        string $mimeType,
        ?int $width
    ): array
    {
        if (
            $mimeType === 'image/png' ||
            $mimeType === 'image/jpeg' ||
            $mimeType === 'image/jpg' ||
            $mimeType === 'image/webp'
        ) {
            try {
                $manager = new ImageManager(
                    Driver::class,
                    autoOrientation: false,
                );

                /**
                 * choosen quality:
                 * it was 100 previously. It took 500MB of memory and 10 seconds to convert a 6MB webp image.
                 * 2026-07-11: changed to 82
                 * also, changing to imagick reduced processing time from 10s to 4s
                 */
                $image = $manager->decodeStream($contentStream);

                if ($width !== null) {
                    $image->scale(width: $width);
                }

                $contentStream = $image->encodeUsingFormat(Format::WEBP, quality: 82)->toStream();
                $mimeType = 'image/webp';

            } catch (\Exception) {
                // ignore
            }
        }

        return [
            'content' => stream_get_contents($contentStream),
            'mimeType' => $mimeType
        ];
    }
}
