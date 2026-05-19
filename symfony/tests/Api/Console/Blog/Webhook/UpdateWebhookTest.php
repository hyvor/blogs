<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
class UpdateWebhookTest extends ApiTestCase
{
    public function test_update_webhook(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-update'],
            ['hyvor_user_id' => 103, 'status' => 'active'],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'url' => 'https://old.com/hook',
            'events' => ['post.created'],
        ]);

        $authUser = AuthFake::generateUser(['id' => 103]);
        $this->consoleBlogApi('PATCH', 'wh-update', '/webhook/' . $webhook->getId(), [
            'url' => 'https://new.com/hook',
        ], user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('https://new.com/hook', $json['url']);
    }

    public function test_update_webhook_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-upd-b1'],
            ['hyvor_user_id' => 104, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-upd-b2'],
            ['hyvor_user_id' => 105, 'status' => 'active'],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 104]);
        $this->consoleBlogApi('PATCH', 'wh-upd-b1', '/webhook/' . $webhook->getId(), [
            'url' => 'https://hack.com/hook',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }
}
