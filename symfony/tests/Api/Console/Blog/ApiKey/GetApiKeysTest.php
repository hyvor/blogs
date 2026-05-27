<?php

namespace App\Tests\Api\Console\Blog\ApiKey;

use App\Api\Console\Controller\ApiKeyController;
use App\Entity\Enum\ApiKeyType;
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
            ['status' => 'active'],
        );

        ApiKeyFactory::createOne([
            'blog' => $blog,
            'name' => 'My Key',
            'type' => ApiKeyType::DELIVERY,
            'api_key' => 'abc123',
        ]);

        $this->consoleBlogApi('GET', 'ak-list', '/api-keys', user: $user);

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
        BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-denied'],
            ['status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'ak-denied', '/api-keys', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
