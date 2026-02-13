<?php

namespace App\Entity\Enum;

enum BlogHostingAt: string
{
    case SUBDOMAIN = 'subdomain';
    case DOMAIN = 'domain';
    case SELF = 'self';
}
