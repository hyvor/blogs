<?php

namespace App\Tests\Api\Public;

use App\Api\Public\HighlightController;
use App\Tests\Case\ApiTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HighlightController::class)]
class HighlightControllerTest extends ApiTestCase
{
    public function test_returns_highlighting_docs(): void
    {
        $this->client->request('GET', '/api/public/highlighting-docs');

        $this->assertResponseIsSuccessful();
        /** @var array<string, mixed> $json */
        $json = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertGreaterThan(0, $json['languagesCount']);
        $this->assertGreaterThan(0, $json['themesCount']);
        $this->assertNotEmpty($json['languageTags']);
        $this->assertNotEmpty($json['themeTags']);
        $this->assertIsString($json['previews']);
        $this->assertStringContainsString('<pre', $json['previews']);
    }

    public function test_has_cors_header_for_any_origin(): void
    {
        $this->client->request(
            'GET',
            '/api/public/highlighting-docs',
            server: ['HTTP_ORIGIN' => 'https://example.com'],
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', 'https://example.com');
    }
}
