<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Service\Delivery\CacheControl;
use App\Service\Delivery\DeliveryResponse;
use App\Service\Delivery\MediaService;
use App\Service\Delivery\MimeTypes;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use Intervention\Image\ImageManager;

class MediaProcessor
{
    public function __construct(private MediaService $mediaService) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $fileName = $matchedRoute->param('file_name');
        if ($fileName === null) {
            return null;
        }

        $additional = $matchedRoute->param('additional');

        $media = $this->mediaService->getByBlogAndName($blog->getId(), $fileName);
        if ($media === null) {
            return null;
        }

        $content = $this->mediaService->getContents($media);
        if ($content === null) {
            return null;
        }

        $mimeType = $media->getExtension()
            ? (MimeTypes::getMimeFromExtension($media->getExtension()) ?? 'application/octet-stream')
            : (MimeTypes::getMimeFromFileName($media->getName()) ?? 'application/octet-stream');

        ['content' => $content, 'mimeType' => $mimeType] = $this->convertImage($content, $mimeType);

        if ($additional !== null) {
            $result = $this->addAdditional($content, $mimeType, $additional);
            if ($result === null) {
                return null;
            }
            $content = $result['content'];
            $mimeType = $result['mimeType'];
        }

        return DeliveryResponse::forFile($content, $mimeType, cacheControl: CacheControl::ONE_YEAR);
    }

    /** @return array{content: string, mimeType: string}|null */
    private function addAdditional(string $content, string $mimeType, string $additional): ?array
    {
        if ($mimeType === 'image/webp' && preg_match('/^(\d+)w$/', $additional, $matches)) {
            $width = (int)$matches[1];
            $manager = new ImageManager(['driver' => 'gd']);
            $image = $manager->make($content)->widen($width, function ($constraint) {
                $constraint->upsize();
            })->encode('webp', 100);
            return ['content' => (string)$image, 'mimeType' => $mimeType];
        }
        return null;
    }

    /** @return array{content: string, mimeType: string} */
    private function convertImage(string $content, string $mimeType): array
    {
        if ($mimeType === 'image/png' || $mimeType === 'image/jpeg') {
            try {
                $manager = new ImageManager(['driver' => 'gd']);
                $content = (string)$manager->make($content)->encode('webp', 100);
                $mimeType = 'image/webp';
            } catch (\Exception) {
                // ignore conversion failure
            }
        }
        return ['content' => $content, 'mimeType' => $mimeType];
    }
}
