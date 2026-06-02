<?php

namespace App\Service\Delivery;

use App\Entity\Enum\RedirectType;

class DeliveryResponse
{
    private function __construct(
        public readonly DeliveryResponseType $type,
        public readonly int $status,
        public readonly ?string $to = null,
        public readonly ?string $content = null,
        public readonly string $mimeType = 'text/html',
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
        return new self(DeliveryResponseType::NOT_FOUND, 404, content: $content);
    }

    public static function forFile(string $content, string $mimeType = 'text/html', int $status = 200): self
    {
        return new self(DeliveryResponseType::FILE, $status, content: $content, mimeType: $mimeType);
    }
}
