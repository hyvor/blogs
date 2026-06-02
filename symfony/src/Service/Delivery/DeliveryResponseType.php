<?php

namespace App\Service\Delivery;

enum DeliveryResponseType
{
    case REDIRECT;
    case NOT_FOUND;
    case FILE;
}
