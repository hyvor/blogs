<?php

namespace App\Entity\Enum;

enum TlsCertificateStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case FAILED = 'failed';
}
