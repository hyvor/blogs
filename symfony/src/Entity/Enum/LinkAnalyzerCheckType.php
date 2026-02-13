<?php

namespace App\Entity\Enum;

enum LinkAnalyzerCheckType: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
}
