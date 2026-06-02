<?php

namespace App\Service\Delivery;

enum DeliveryResponseType
{
    case REDIRECT;
    case FILE;
}
