<?php

namespace App\Service\Hosting\CustomDomain;

use App\Service\Hosting\CustomDomain\Exception\InternalCustomDomainVerificationException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InternalCustomDomainVerificationService
{

    public const string CACHE_KEY_PREFIX = 'internal-dns-verification-';

    public function __construct(
        private HttpClientInterface $http,
        private ClockInterface $clock,
        private CacheItemPoolInterface $cache,
    ) {}

    /**
     * @throws InternalCustomDomainVerificationException
     */
    public function verify(string $domain): void
    {
        $token = bin2hex(random_bytes(16));
        $url = "http://{$domain}/.well-known/hyvor-blogs-verification.txt";

        $item = $this->cache->getItem(self::CACHE_KEY_PREFIX . $domain);
        $item->set($token);
        $this->cache->save($item);

        $attempt = 0;
        $maxAttempts = 3;
        $sleepSeconds = 3;

        $lastError = null;

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
                $lastError = $e;
            } finally {
                $attempt++;
                $this->clock->sleep($sleepSeconds);
            }
        }

        throw new InternalCustomDomainVerificationException('Domain not pointed', previous: $lastError);
    }

    public function getVerificationToken(string $domain): ?string
    {
        $item = $this->cache->getItem(self::CACHE_KEY_PREFIX . $domain);
        return $item->isHit() ? $item->get() : null;
    }
}
