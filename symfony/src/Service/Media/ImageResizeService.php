<?php declare(strict_types=1);

namespace App\Service\Media;

use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

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
        $manager = new ImageManager(Driver::class, autoOrientation: false);
        return $manager->decodeBinary($content)->width();
    }
}
