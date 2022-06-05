<?php
namespace App\Data\Enums;

enum DeliveryAPIFileTypeEnum : string
{

    case TEMPLATE = 'template';
    case ASSET = 'asset';
    case MEDIA = 'media';
    case PREVIEW = 'preview';
    case STYLES = 'styles';

}