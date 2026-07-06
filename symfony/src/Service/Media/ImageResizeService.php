<?php declare(strict_types=1);

namespace App\Service\Media;

use Intervention\Image\ImageManagerStatic as Image;

class ImageResizeService
{
    private const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function isMimeTypeSupported(?string $mimeType): bool
    {
        return in_array($mimeType, self::SUPPORTED_MIME_TYPES);
    }

    public function getImageWidth(string $content): int
    {
        return Image::make($content)->width();
    }
}
