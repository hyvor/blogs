<?php

namespace App\Tests\Api\Misc;

use App\Api\Delivery\CustomDomainController;
use App\Tests\Case\ApiTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\Cache\CacheInterface;

#[CoversClass(CustomDomainController::class)]
class LetsEncryptTest extends ApiTestCase
{
    public function test_returns_404_for_unknown_token(): void
    {
        $this->client->request('GET', '/.well-known/acme-challenge/unknown-token');
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_returns_key_auth_for_known_token(): void
    {
        $cache = $this->getContainer()->get(CacheInterface::class);
        $this->assertInstanceOf(CacheInterface::class, $cache);
        $cache->get('acme_challenge_test-token-123', fn() => 'test-key-auth-value');

        $this->client->request('GET', '/.well-known/acme-challenge/test-token-123');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('text/plain', $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertSame('test-key-auth-value', $this->client->getResponse()->getContent());
    }
}
