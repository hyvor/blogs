<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

enum IgnoreReasonEnum: string
{

    // cloudflare, etc.
    case KNOWN_FIREWALL = 'known_firewall';

    // blocked by robots.txt
    // this is not used currently, but may implement later
    case ROBOTS_TXT = 'robots_txt';

    // something went wrong on our side
    case INTERNAL_ERROR = 'internal_error';

}
