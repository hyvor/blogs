<?php
namespace App\Data\Enums;

/**
 * Handling Scopes
 */

enum DeliveryAPIScopeEnum {

    case INDEX;
    case POST;
    case PAGE;
    case AUTHOR;
    case TAG;
    case SEARCH;

}