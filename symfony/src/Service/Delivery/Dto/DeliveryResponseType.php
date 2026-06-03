<?php

namespace App\Service\Delivery\Dto;

enum DeliveryResponseType
{
    case REDIRECT;
    case FILE;
}
