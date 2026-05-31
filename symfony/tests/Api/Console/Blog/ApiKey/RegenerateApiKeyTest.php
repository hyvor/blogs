<?php

namespace App\Tests\Api\Console\Blog\ApiKey;

use App\Api\Console\Controller\ApiKeyController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeyController::class)]
class RegenerateApiKeyTest extends ApiTestCase
{
    public function test_regenerate_api_key(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-regen'],
            ['status' => 'active'],
        );
        $apiKey = ApiKeyFactory::createOne([
            'blog' => $blog,
            'api_key' => 'oldkey12345678901234567890abcd',
        ]);

        $this->consoleBlogApi('PATCH', 'ak-regen', '/api-key/' . $apiKey->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('api_key', $json);
        $this->assertNotSame('oldkey12345678901234567890abcd', $json['api_key']);
    }

    public function test_regenerate_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-rg-b1'],
            ['status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-rg-b2'],
            ['status' => 'active'],
        );
        $apiKey = ApiKeyFactory::createOne([
            'blog' => $blog2,
        ]);

        $this->consoleBlogApi('PATCH', 'ak-rg-b1', '/api-key/' . $apiKey->getId(), user: $user1);

        $this->assertResponseStatusCodeSame(404);
    }
}
