<?php

namespace App\Service\LinkAnalysis\StatusCheck;

enum IgnoreReason: string
{
    case KNOWN_FIREWALL = 'known_firewall';
    case ROBOTS_TXT = 'robots_txt';
    case INTERNAL_ERROR = 'internal_error';
}
