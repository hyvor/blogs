<?php

namespace App\Data\Enums;

enum MediaHostedAtEnum: string
{
    case PLATFORM = 'platform';
    case CUSTOM_S3 = 'custom_s3';
}
