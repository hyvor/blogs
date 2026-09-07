<?php

namespace Api\Sudo;

use App\Api\Sudo\Controller\SudoController;
use App\Tests\Case\ApiTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SudoController::class)]
class InitSudoTest extends ApiTestCase
{

    public function test_requires_authentication(): void
    {
        $this->sudoApi('GET', '/init');

        $this->assertResponseFailed(403, 'auth_required');
    }

    public function test_requires_sudo_access(): void
    {
        $this->sudoApi('GET', '/init', user: 123, sudoRole: null);

        $this->assertResponseFailed(403, 'You do not have sudo access.');
    }

    public function test_returns_init_data_for_sudo_user(): void
    {
        $this->sudoApi('GET', '/init', user: 123);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertIsArray($json['config']);
        $this->assertIsArray($json['config']['hyvor']);
        $this->assertArrayHasKey('instance', $json['config']['hyvor']);
        $this->assertIsArray($json['config']['app']);
        $this->assertArrayHasKey('delivery_url', $json['config']['app']);

        $this->assertIsArray($json['stats']);
        $this->assertArrayHasKey('total_blogs', $json['stats']);
        $this->assertArrayHasKey('total_30d_change', $json['stats']);
        $this->assertArrayHasKey('blogs_with_custom_domains', $json['stats']);
    }

}
