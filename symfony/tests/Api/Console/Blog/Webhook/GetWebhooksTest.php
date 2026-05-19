<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
class GetWebhooksTest extends ApiTestCase
{
    public function test_get_webhooks(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-test'],
            ['hyvor_user_id' => 100, 'status' => 'active'],
        );
        WebhookFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'url' => 'https://example.com/hook',
            'events' => ['post.created'],
            'secret' => 'mysecret1234567890123456789012',
        ]);

        $authUser = AuthFake::generateUser(['id' => 100]);
        $this->consoleBlogApi('GET', 'wh-test', '/webhooks', user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json);
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame('https://example.com/hook', $json[0]['url']);
        $this->assertSame(['post.created'], $json[0]['events']);
        $this->assertArrayHasKey('secret', $json[0]);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-denied'],
            ['hyvor_user_id' => 110, 'status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'wh-denied', '/webhooks', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_blog_not_found(): void
    {
        $authUser = AuthFake::generateUser(['id' => 111]);
        $this->consoleBlogApi('GET', 'nonexistent-blog-xyz', '/webhooks', user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }
}
