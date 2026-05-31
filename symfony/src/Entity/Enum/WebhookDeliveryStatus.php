<?php

namespace App\Entity\Enum;

enum WebhookDeliveryStatus: string
{
    case PENDING = 'pending';
    case RETRYING = 'retrying';
    case FAILED = 'failed';
    case SUCCESS = 'success';
}
