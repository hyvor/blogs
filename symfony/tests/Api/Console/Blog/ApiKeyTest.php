<?php

namespace Api\Console\Blog;

use App\Api\Console\Controller\ApiKeyController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeyController::class)]
class ApiKeyTest extends ApiTestCase
{
    public function test_get_api_keys(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-list'],
            ['hyvor_user_id' => 200, 'status' => 'active'],
        );
        ApiKeyFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'name' => 'My Key',
            'type' => 'delivery',
            'api_key' => 'abc123',
        ]);

        $authUser = AuthFake::generateUser(['id' => 200]);
        $this->consoleBlogApi('GET', 'ak-list', '/api-keys', user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json);
        $this->assertCount(1, $json);
        $this->assertSame('My Key', $json[0]['name']);
        $this->assertSame('delivery', $json[0]['type']);
    }

    public function test_create_api_key(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-create'],
            ['hyvor_user_id' => 201, 'status' => 'active'],
        );

        $authUser = AuthFake::generateUser(['id' => 201]);
        $this->consoleBlogApi('POST', 'ak-create', '/api-keys', [
            'name' => 'Test Key',
            'type' => 'console',
        ], user: $authUser);

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
            ['hyvor_user_id' => 202, 'status' => 'active'],
        );
        for ($i = 0; $i < 50; $i++) {
            ApiKeyFactory::createOne([
                'blog' => $blog,
                'blog_id' => $blog->getId(),
                'api_key' => bin2hex(random_bytes(16)),
            ]);
        }

        $authUser = AuthFake::generateUser(['id' => 202]);
        $this->consoleBlogApi('POST', 'ak-limit', '/api-keys', [
            'name' => 'Over Limit',
            'type' => 'console',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_regenerate_api_key(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-regen'],
            ['hyvor_user_id' => 203, 'status' => 'active'],
        );
        $apiKey = ApiKeyFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'api_key' => 'oldkey12345678901234567890abcd',
        ]);

        $authUser = AuthFake::generateUser(['id' => 203]);
        $this->consoleBlogApi('PATCH', 'ak-regen', '/api-keys/' . $apiKey->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('api_key', $json);
        $this->assertNotSame('oldkey12345678901234567890abcd', $json['api_key']);
    }

    public function test_regenerate_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-rg-b1'],
            ['hyvor_user_id' => 204, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-rg-b2'],
            ['hyvor_user_id' => 205, 'status' => 'active'],
        );
        $apiKey = ApiKeyFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 204]);
        $this->consoleBlogApi('PATCH', 'ak-rg-b1', '/api-keys/' . $apiKey->getId(), user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_delete_api_key(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-delete'],
            ['hyvor_user_id' => 206, 'status' => 'active'],
        );
        $apiKey = ApiKeyFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 206]);
        $this->consoleBlogApi('DELETE', 'ak-delete', '/api-keys/' . $apiKey->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-denied'],
            ['hyvor_user_id' => 207, 'status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'ak-denied', '/api-keys', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
