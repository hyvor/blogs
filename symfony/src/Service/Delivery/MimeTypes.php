<?php

namespace App\Service\Delivery;

use Symfony\Component\Mime\MimeTypes as SymfonyMimeTypes;

class MimeTypes
{
    public static function getMimeFromExtension(string $extension): ?string
    {
        $types = (new SymfonyMimeTypes())->getMimeTypes($extension);
        return $types[0] ?? null;
    }

    public static function getMimeFromFileName(string $name): ?string
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        return $extension ? self::getMimeFromExtension($extension) : null;
    }
}
