<?php

namespace App\Tests\Api\Misc;

use App\Api\Misc\MiscController;
use App\Tests\Case\ApiTestCase;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MiscController::class)]
class ServeMediaTest extends ApiTestCase
{
    public function test_serves_an_existing_file(): void
    {
        $filesystem = $this->getService(Filesystem::class);
        $filesystem->write('exports/test-export.txt', 'hello world');

        $this->client->request('GET', '/api/media/exports/test-export.txt');

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertSame('hello world', $response->getContent());
        $this->assertStringContainsString('public', (string) $response->headers->get('Cache-Control'));
        $this->assertNotNull($response->getEtag());
        $this->assertSame(31536000, $response->getMaxAge());
    }

    public function test_returns_404_for_missing_file(): void
    {
        $this->client->request('GET', '/api/media/exports/does-not-exist.txt');

        $this->assertResponseStatusCodeSame(404);
    }
}
