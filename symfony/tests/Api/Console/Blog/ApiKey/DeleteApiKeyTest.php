<?php

namespace App\Tests\Api\Console\Blog\ApiKey;

use App\Api\Console\Controller\ApiKeyController;
use App\Entity\ApiKey;
use App\Service\ApiKey\ApiKeyService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeyController::class)]
#[CoversClass(ApiKeyService::class)]
class DeleteApiKeyTest extends ApiTestCase
{
    public function test_delete_api_key(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'ak-delete'],
            ['status' => 'active'],
        );
        $apiKey = ApiKeyFactory::createOne([
            'blog' => $blog,
        ]);

        $id = $apiKey->getId();
        $this->consoleBlogApi('DELETE', 'ak-delete', '/api-key/' . $apiKey->getId(), user: $user);

        $this->assertResponseIsSuccessful();

        $this->assertNull(
            $this->getEm()->getRepository(ApiKey::class)->find($id)
        );
    }
}
