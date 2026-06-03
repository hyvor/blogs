<?php

namespace App\Service\Delivery\Dto;

use App\Entity\Enum\RedirectType;

class DeliveryResponse
{
    private function __construct(
        public readonly DeliveryResponseType $type,
        public readonly int $status,
        public readonly ?string $to = null,
        public readonly ?string $content = null,
        public readonly string $mimeType = 'text/html',
        public readonly CacheControl $cacheControl = CacheControl::NO_CACHE,
        public readonly ?DeliveryFileType $fileType = null,
    ) {}

    public static function forRedirect(string $to, RedirectType $type): self
    {
        return new self(
            DeliveryResponseType::REDIRECT,
            $type === RedirectType::PERMANENT ? 301 : 302,
            to: $to,
        );
    }

    public static function forNotFound(string $content = '404'): self
    {
        return new self(
            DeliveryResponseType::FILE,
            404,
            content: $content,
            fileType: DeliveryFileType::TEMPLATE,
        );
    }

    public static function forFile(
        DeliveryFileType $fileType,
        string $content,
        string $mimeType = 'text/html',
        int $status = 200,
        CacheControl $cacheControl = CacheControl::NO_CACHE,
    ): self {
        return new self(
            DeliveryResponseType::FILE,
            $status,
            content: $content,
            mimeType: $mimeType,
            cacheControl: $cacheControl,
            fileType: $fileType,
        );
    }

    public static function forError(
        DeliveryFileType $fileType,
        string $content = '',
        int $status = 500,
    ): self {
        return new self(
            DeliveryResponseType::FILE,
            $status,
            content: $content,
            mimeType: 'text/plain',
            cacheControl: CacheControl::NO_CACHE,
            fileType: $fileType,
        );
    }
}
