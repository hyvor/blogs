<?php

namespace App\Service\Delivery;

enum CacheControl
{
    case NO_CACHE;
    case ONE_WEEK;
    case ONE_YEAR;
}
