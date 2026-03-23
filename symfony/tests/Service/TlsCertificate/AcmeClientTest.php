<?php

namespace App\Tests\Service\TlsCertificate;

use App\Service\TlsCertificate\Acme\AcmeClient;
use App\Tests\Case\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversNamespace;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[CoversNamespace("App\Service\TlsCertificate\Acme")]
class AcmeClientTest extends KernelTestCase
{
    public function test_acme_client(): void
    {
        $client = $this->getService(AcmeClient::class);
        $cache = $this->getService(CacheInterface::class);

        $client->init();
        $order = $client->newOrder('nadil.me');
        $token = $order->token;
        $keyAuthorization = $order->keyAuthorization;

        $cache->get('acme_challenge_' . $token, function (ItemInterface $item) use ($keyAuthorization) {
            $item->expiresAfter(300); // 5 minutes
            return $keyAuthorization;
        });


    }

}