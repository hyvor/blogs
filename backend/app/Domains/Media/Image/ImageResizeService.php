<?php declare(strict_types=1);

namespace App\Domains\Media\Image;

use Intervention\Image\Facades\Image;

class ImageResizeService
{

    private const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    public static function isMimeTypeSupported(?string $mimeType): bool
    {
        return in_array($mimeType, self::SUPPORTED_MIME_TYPES);
    }

    public static function getImageWidth(string $content) : int
    {
        return Image::make($content)->width();
    }

}