<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck\Ignore;

enum KnownFirewallEnum: string
{

    case CLOUDFLARE_CHALLENGE = 'cloudflare_challenge';

}
