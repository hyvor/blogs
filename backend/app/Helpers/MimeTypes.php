<?php

namespace App\Helpers;

use Symfony\Component\Mime\MimeTypes as SymfonyMimeTypes;

class MimeTypes
{
    public static function getMimeFromExtension(string $extension): ?string
    {
        $mimeTypes = new SymfonyMimeTypes();
        $types = $mimeTypes->getMimeTypes($extension);

        return $types[0] ?? null;
    }

    public static function getMimeFromFileName(string $name) : ?string
    {

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        return self::getMimeFromExtension($extension);

    }
}
