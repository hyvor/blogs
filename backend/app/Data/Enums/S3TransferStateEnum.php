<?php

namespace App\Data\Enums;

enum S3TransferStateEnum: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
