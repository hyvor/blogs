<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

enum IgnoreReasonEnum: string
{

    // cloudflare, etc.
    case KNOWN_FIREWALL = 'known_firewall';

    // blocked by robots.txt
    // TODO: this is not used
    case ROBOTS_TXT = 'robots_txt';

    case INTERNAL_ERROR = 'internal_error';

}
