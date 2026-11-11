<?php

namespace App\Tests\Api\Console\Blog\ApiKey;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Controller\ApiKeyController;
use App\Api\Console\Object\ApiKeyObject;
use App\Entity\ApiKey;
use App\Entity\Enum\ApiKeyType;
use App\Entity\Enum\UserStatus;
use App\Service\ApiKey\ApiKeyService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeyController::class)]
#[CoversClass(ApiKeyObject::class)]
#[CoversClass(ApiKeyService::class)]
class CreateApiKeyTest extends ApiTestCase
{
    public function test_create_api_key(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-create'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', 'ak-create', '/api-key', [
            'name' => 'Test Key',
            'type' => 'console',
            'scopes' => ['posts.read', 'tags.write', 'posts.read'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('Test Key', $json['name']);
        $this->assertSame('console', $json['type']);
        $this->assertArrayHasKey('api_key', $json);
        $this->assertSame(['posts.read', 'tags.write'], $json['scopes']);

        $apiKey = $this->getEm()->getRepository(ApiKey::class)->find($json['id']);
        $this->assertNotNull($apiKey);
        $this->assertSame([Scope::POSTS_READ, Scope::TAGS_WRITE], $apiKey->getScopes());
    }

    public function test_create_console_api_key_requires_scopes(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-no-scopes'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', 'ak-no-scopes', '/api-key', [
            'name' => 'Test Key',
            'type' => 'console',
            'scopes' => [],
        ], user: $user);

        $this->assertResponseFailed(422, 'At least one scope is required');
    }

    public function test_create_api_key_with_invalid_scope(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-invalid-scope'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', 'ak-invalid-scope', '/api-key', [
            'name' => 'Test Key',
            'type' => 'console',
            'scopes' => ['posts.read', 'foo.bar'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSame(0, $this->getEm()->getRepository(ApiKey::class)->count(['blog' => $blog]));
    }

    public function test_api_key_cannot_create_api_key_with_more_scopes(): void
    {
        [$blog] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-escalation'],
            ['status' => UserStatus::ACTIVE],
        );

        ApiKeyFactory::createOne([
            'blog' => $blog,
            'api_key' => 'escalationkey1234567890123456789',
            'type' => ApiKeyType::CONSOLE,
            'scopes' => ['api_keys.write'],
        ]);

        $this->consoleBlogApi('POST', 'ak-escalation', '/api-key', [
            'name' => 'Escalated Key',
            'type' => 'console',
            'scopes' => ['api_keys.write', 'posts.write'],
        ], server: ['HTTP_X_API_KEY' => 'escalationkey1234567890123456789']);

        $this->assertResponseFailed(422, "You cannot grant the scope 'posts.write' as you do not have it");

        // same or fewer scopes are allowed
        $this->consoleBlogApi('POST', 'ak-escalation', '/api-key', [
            'name' => 'Same Key',
            'type' => 'console',
            'scopes' => ['api_keys.write'],
        ], server: ['HTTP_X_API_KEY' => 'escalationkey1234567890123456789']);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame(['api_keys.write'], $this->getJson()['scopes']);
    }

    public function test_create_delivery_api_key_ignores_scopes(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-delivery'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', 'ak-delivery', '/api-key', [
            'name' => 'Delivery Key',
            'type' => 'delivery',
            'scopes' => ['posts.write'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('delivery', $json['type']);
        $this->assertSame([], $json['scopes']);
    }

    public function test_create_api_key_limit(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-limit'],
            ['status' => UserStatus::ACTIVE],
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
            'scopes' => ['posts.read'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }
}
