<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck\Ignore;

class KnownFirewall
{

    /**
     * @param array<string, string> $headers
     */
    public static function isKnownFirewall(array $headers): ?KnownFirewallEnum
    {
        if (self::isCloudflareChallenge($headers)) {
            return KnownFirewallEnum::CLOUDFLARE_CHALLENGE;
        }

        return null;
    }

    /**
     * @param array<string, string> $headers
     */
    public static function isCloudflareChallenge(array $headers): bool
    {
        if (
            isset($headers['cf-mitigated']) &&
            $headers['cf-mitigated'] === 'challenge'
        ) {
            return true;
        }

        return false;
    }

}