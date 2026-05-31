<?php

namespace App\Tests\Api\Console\Blog\ApiKey;

use App\Api\Console\Controller\ApiKeyController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeyController::class)]
class CreateApiKeyTest extends ApiTestCase
{
    public function test_create_api_key(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-create'],
            ['status' => 'active'],
        );

        $this->consoleBlogApi('POST', 'ak-create', '/api-key', [
            'name' => 'Test Key',
            'type' => 'console',
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('Test Key', $json['name']);
        $this->assertSame('console', $json['type']);
        $this->assertArrayHasKey('api_key', $json);
    }

    public function test_create_api_key_limit(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-limit'],
            ['status' => 'active'],
        );
        for ($i = 0; $i < 50; $i++) {
            ApiKeyFactory::createOne([
                'blog' => $blog,
                'api_key' => bin2hex(random_bytes(16)),
            ]);
        }

        $this->consoleBlogApi('POST', 'ak-limit', '/api-key', [
            'name' => 'Over Limit',
            'type' => 'console',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }
}
