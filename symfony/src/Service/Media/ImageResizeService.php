<?php declare(strict_types=1);

namespace App\Service\Media;

use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ExceptionInterface as ImageExceptionInterface;

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

    /**
     * Returns null when the binary cannot be decoded as an image (corrupt upload,
     * mislabelled extension, unsupported format) so callers can degrade gracefully.
     */
    public function getImageWidth(string $content): ?int
    {
        $manager = new ImageManager(Driver::class, autoOrientation: false);

        try {
            return $manager->decodeBinary($content)->width();
        } catch (ImageExceptionInterface) {
            return null;
        }
    }
}
