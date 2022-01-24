<?php
namespace App\Data\Enums;

enum RedirectTypeEnum: int {

    case PERMANENT  = 301;
    case TEMPORARY = 302;

}