<?php

namespace App\Service\Delivery\Dto;

enum CacheControl
{
    case NO_CACHE;
    case ONE_WEEK;
    case ONE_YEAR;

    public function toHeaderValue(): string
    {
        return match ($this) {
            self::NO_CACHE => 'no-cache, no-store, must-revalidate',
            self::ONE_WEEK => 'public, max-age=604800',
            self::ONE_YEAR => 'public, max-age=31536000',
        };
    }
}
