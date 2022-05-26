<?php

namespace App\Data\Enums;

enum BlogHostingAtEnum : string
{
    case SUBDOMAIN = 'subdomain';
    case DOMAIN = 'domain';
    case self = 'self';
}
