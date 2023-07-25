<?php declare(strict_types=1);

namespace App\Data\Enums;

enum CacheClearTypeEnum : string
{

     case ALL = 'all';
     case TEMPLATE = 'template';
     case PATHS = 'paths';
}
