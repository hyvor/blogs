<?php
namespace App\Data\Enums;

/**
 * Handling Scopes
 */

enum DeliveryAPIScopeEnum : string {

    case INDEX = 'index';
    case POST  = 'post';
    case PAGE  = 'page';
    case AUTHOR  = 'author';
    case TAG = 'tag';
    case SEARCH = 'search';

}