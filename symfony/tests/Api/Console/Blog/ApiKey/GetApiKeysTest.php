<?php

namespace App\Tests\Api\Console\Blog\ApiKey;

use App\Api\Console\Controller\ApiKeyController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeyController::class)]
class GetApiKeysTest extends ApiTestCase
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
        $this->assertIsArray($json[0]);
        $this->assertSame('My Key', $json[0]['name']);
        $this->assertSame('delivery', $json[0]['type']);
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
