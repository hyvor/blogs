<?php

namespace App\Entity\Enum;

enum HostingChangeStatus: string
{
    case CHANGING = 'changing';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
