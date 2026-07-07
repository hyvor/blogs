<?php

namespace App\Service\CustomDomain;

use App\Service\CustomDomain\Exception\InternalCustomDomainVerificationException;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * verifies that DNS for a given domain is correctly pointed to Hyvor Blogs
 * creates a temporary token that works the same way as the ACME challenge,
 * and checks if we can reach it via HTTP using the user's domain.
 */
class InternalCustomDomainVerificationService
{

    use ClockAwareTrait;

    public const string CACHE_KEY_PREFIX = 'internal-dns-verification-';

    public function __construct(
        private CacheInterface $cache,
        private HttpClientInterface $http,
        private ClockInterface $clock
    ) {}

    /**
     * @throws InternalCustomDomainVerificationException
     */
    public function verify(string $domain): void
    {
        $token = bin2hex(random_bytes(16));
        $url = "http://{$domain}/.well-known/hyvor-blogs-verification.txt";
        $this->cache->get(self::CACHE_KEY_PREFIX . $domain, fn() => $token);

        $attempt = 0;
        $maxAttempts = 3;
        $sleepSeconds = 3;

        while ($attempt < $maxAttempts) {
            try {
                $response = $this->http->request(
                    'GET',
                    $url,
                    [
                        'timeout' => 5,
                    ]
                );

                if ($response->getContent() === $token) {
                    return;
                }

            } catch (ExceptionInterface $e) {
            } finally {
                $attempt++;
                $this->clock->sleep($sleepSeconds);
            }
        }

        throw new InternalCustomDomainVerificationException('Domain not pointed');
    }

    public function getVerificationToken(string $domain): ?string
    {
        return $this->cache->get(self::CACHE_KEY_PREFIX . $domain, fn() => null);
    }

}
