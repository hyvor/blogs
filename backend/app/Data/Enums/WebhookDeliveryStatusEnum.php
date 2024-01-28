<?php

namespace App\Data\Enums;

enum WebhookDeliveryStatusEnum: string
{
    // hasn't started yet
    case PENDING = 'pending';
    // failed but retying
    case RETRYING = 'retrying';
    // failed and stopped retring
    case FAILED = 'failed';
    // successful
    case SUCCESS = 'success';
}
