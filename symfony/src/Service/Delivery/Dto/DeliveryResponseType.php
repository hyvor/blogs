<?php

namespace App\Service\Delivery\Dto;

enum DeliveryResponseType: string
{
    case REDIRECT = 'redirect';
    case FILE = 'file';
}
