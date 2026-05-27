<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
class DeleteWebhookTest extends ApiTestCase
{
    public function test_delete_webhook(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-delete'],
            ['status' => 'active'],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);

        $this->consoleBlogApi('DELETE', 'wh-delete', '/webhook/' . $webhook->getId(), user: $user);

        $this->assertResponseIsSuccessful();
    }

    public function test_delete_webhook_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-del-b1'],
            ['status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-del-b2'],
            ['status' => 'active'],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $this->consoleBlogApi('DELETE', 'wh-del-b1', '/webhook/' . $webhook->getId(), user: $user1);

        $this->assertResponseStatusCodeSame(404);
    }
}
