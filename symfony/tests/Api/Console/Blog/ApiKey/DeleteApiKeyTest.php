<?php

namespace App\Tests\Api\Console\Blog\ApiKey;

use App\Api\Console\Controller\ApiKeyController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeyController::class)]
class DeleteApiKeyTest extends ApiTestCase
{
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
        $this->consoleBlogApi('DELETE', 'ak-delete', '/api-key/' . $apiKey->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
    }
}
