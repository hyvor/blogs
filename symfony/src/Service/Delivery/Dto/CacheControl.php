<?php

namespace App\Service\Delivery\Dto;

use JsonSerializable;

enum CacheControl implements JsonSerializable
{
    case NO_CACHE;
    case ONE_HOUR;
    case ONE_WEEK;
    case ONE_YEAR;

    public function toHeaderValue(): string
    {
        return match ($this) {
            self::NO_CACHE => 'no-cache, private',
            self::ONE_HOUR => 'public, max-age=3600',
            self::ONE_WEEK => 'public, max-age=604800',
            self::ONE_YEAR => 'public, max-age=31536000',
        };
    }

    public function jsonSerialize(): string
    {
        return $this->toHeaderValue();
    }
}
