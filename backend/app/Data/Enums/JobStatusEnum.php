<?php declare(strict_types=1);

namespace App\Data\Enums;

enum JobStatusEnum : string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
