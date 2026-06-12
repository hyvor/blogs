<?php

namespace App\Service\Delivery\Dto;

enum DeliveryFileType: string
{
    case TEMPLATE = 'template';
    case ASSET = 'asset';
    case MEDIA = 'media';
}
