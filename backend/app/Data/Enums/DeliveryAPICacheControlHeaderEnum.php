<?php declare(strict_types=1);

namespace App\Data\Enums;

enum DeliveryAPICacheControlHeaderEnum : string
{

    case NO_CACHE = 'no-cache, private';
    case CACHE_ONE_YEAR = 'public, max-age=31536000';
    case CACHE_ONE_WEEK = 'public, max-age=604800';

}
