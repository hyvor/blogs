<?php

namespace App\Tests\Api\Public;

use App\Api\Public\ThemeController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ThemeFactory;
use App\Tests\Factory\ThemeVersionFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ThemeController::class)]
class ThemeControllerTest extends ApiTestCase
{
    public function test_lists_themes_with_latest_version(): void
    {
        $theme = ThemeFactory::createOne(['name' => 'my-theme']);
        ThemeVersionFactory::createOne(['theme' => $theme, 'version' => '1.0.0']);

        $this->client->request('GET', '/api/public/themes');

        $this->assertResponseIsSuccessful();
        /** @var list<array<string, mixed>> $json */
        $json = json_decode((string) $this->client->getResponse()->getContent(), true);
        $names = array_column($json, 'name');
        $this->assertContains('my-theme', $names);

        $row = $json[array_search('my-theme', $names, true)];
        $this->assertSame('1.0.0', $row['latest_version']);
    }

    public function test_has_cors_header_for_any_origin(): void
    {
        $this->client->request(
            'GET',
            '/api/public/themes',
            server: ['HTTP_ORIGIN' => 'https://example.com'],
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', 'https://example.com');
    }
}
