<?php

namespace App\Service\Delivery\Dto;

use App\Entity\Enum\RedirectType;

class DeliveryResponse implements \JsonSerializable
{
 
    public readonly int $at;

    // whether the response was created from our cache
    private bool $fromCache = false;

    private function __construct(
        public readonly DeliveryResponseType $type,
        public readonly int $status,
        public readonly bool $cache = true,
        public readonly ?string $to = null,
        public readonly ?string $content = null,
        public readonly string $mimeType = 'text/html',
        public readonly CacheControl $cacheControl = CacheControl::NO_CACHE,
        public readonly ?DeliveryFileType $fileType = null,
    ) {
        $this->at = time();
    }

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
        bool $cache = true,
        CacheControl $cacheControl = CacheControl::NO_CACHE,
    ): self {
        return new self(
            DeliveryResponseType::FILE,
            $status,
            cache: $cache,
            content: $content,
            mimeType: $mimeType,
            cacheControl: $cacheControl,
            fileType: $fileType,
        );
    }

    public static function forError(
        string $error,
        DeliveryFileType $fileType = DeliveryFileType::TEMPLATE,
        int $status = 500,
    ): self {
        $html = <<<HTML
        <div style=\"font-family:monospace;font-size:18px;\">
            {$error}
        </div>
        HTML;

        return new self(
            DeliveryResponseType::FILE,
            $status,
            cache: false,
            content: $html,
            mimeType: 'text/html',
            cacheControl: CacheControl::NO_CACHE,
            fileType: $fileType,
        );
    }

    public function jsonSerialize(): mixed
    {
        $response = [
            'type' => $this->type->value,
            'status' => $this->status,
            'cache' => $this->cache,
            'cache_control' => $this->cacheControl->toHeaderValue(),
            'at' => $this->at,
        ];

        if ($this->type === DeliveryResponseType::REDIRECT) {
            $response['to'] = $this->to;
        } elseif ($this->type === DeliveryResponseType::FILE) {
            $response['content'] = base64_encode($this->content ?? '');
            $response['file_type'] = $this->fileType?->value;
            $response['mime_type'] = $this->mimeType;
        }

        return $response;
    }

    public function setFromCache(bool $fromCache): void
    {
        $this->fromCache = $fromCache;
    }

    public function isFromCache(): bool
    {
        return $this->fromCache;
    }
}
