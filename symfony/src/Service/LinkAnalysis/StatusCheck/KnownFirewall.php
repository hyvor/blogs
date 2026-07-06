<?php

namespace App\Service\LinkAnalysis\StatusCheck;

enum KnownFirewall: string
{
    case CLOUDFLARE_CHALLENGE = 'cloudflare_challenge';

    /** @param array<string, string> $headers */
    public static function detect(array $headers): ?self
    {
        if (self::isCloudflareChallenge($headers)) {
            return self::CLOUDFLARE_CHALLENGE;
        }

        return null;
    }

    /** @param array<string, string> $headers */
    private static function isCloudflareChallenge(array $headers): bool
    {
        return isset($headers['cf-mitigated']) && $headers['cf-mitigated'] === 'challenge';
    }
}
