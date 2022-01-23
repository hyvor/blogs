<?php
namespace App\Data\Enums;

enum DeliveryAPITypeEnum: string {

    case TEXT = 'text';
    case BINARY = 'binary';
    case REDIRECT = 'redirect';
    case NOTFOUND = 'notfound';

}