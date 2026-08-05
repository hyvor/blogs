<?php

namespace App\Entity\Enum;

/**
 * How TLS is handled for the app domain (DOMAIN_APP).
 * https://blogs.hyvor.com/hosting/deploy#tls
 */
enum TlsMode: string
{
    // Caddy automatically obtains and renews a certificate (Let's Encrypt)
    case AUTO = 'auto';

    // TLS is terminated outside the container (e.g. Nginx, Traefik); the container is reached via HTTP
    case EXTERNAL = 'external';

    // TLS_CERT_FILE and TLS_KEY_FILE are used to serve TLS
    case MANUAL = 'manual';

    // TLS is fully disabled; the app is served over HTTP only
    case DISABLED = 'disabled';

    public function getScheme(): string
    {
        return $this === self::DISABLED ? 'http' : 'https';
    }
}
