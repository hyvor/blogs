<?php

namespace App\Tests\Api\Local;

use App\Api\Local\LocalController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\CustomDomainFactory;
use Hyvor\Internal\Util\Crypt\Encryption;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LocalController::class)]
class GetCaddyCertificateTest extends ApiTestCase
{

    public function test_when_domain_not_found(): void
    {
        $this->setEnvVar('CADDY_ROUTER', 'local');
        $this->client->request('GET', '/api/local/caddy-certificate?server_name=example.com', [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $this->assertResponseStatusCodeSame(404);
        $content = $this->client->getResponse()->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString(
            'domain not found',
            $content
        );
    }

    public function test_when_certificate_not_found(): void
    {
        CustomDomainFactory::createOne([
            'domain' => 'example.com',
            'certificate' => null,
            'private_key_encrypted' => null,
        ]);


        $this->setEnvVar('CADDY_ROUTER', 'local');
        $this->client->request('GET', '/api/local/caddy-certificate?server_name=example.com', [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $this->assertResponseStatusCodeSame(404);
        $content = $this->client->getResponse()->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString(
            'certificate not found',
            $content
        );
    }

    public function test_returns_certificate_and_private_key(): void
    {
        CustomDomainFactory::createOne([
            'domain' => 'example.com',
            'certificate' => 'cert',
            'private_key_encrypted' => $this->getService(Encryption::class)->encryptString('private-key')
        ]);

        $this->setEnvVar('CADDY_ROUTER', 'local');
        $this->client->request('GET', '/api/local/caddy-certificate?server_name=example.com', [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $this->assertResponseIsSuccessful("cert\nprivate-key");
        $this->assertResponseHeaderSame('Content-Type', 'application/x-pem-file');
    }
}
