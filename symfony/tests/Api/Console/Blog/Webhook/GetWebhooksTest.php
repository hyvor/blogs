<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Api\Console\Object\WebhookObject;
use App\Entity\Enum\WebhookEvent;
use App\Service\Webhook\WebhookService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
#[CoversClass(WebhookObject::class)]
#[CoversClass(WebhookService::class)]
class GetWebhooksTest extends ApiTestCase
{
    public function test_get_webhooks(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-test'],
            ['status' => 'active'],
        );
        WebhookFactory::createOne([
            'blog' => $blog,
            'url' => 'https://example.com/hook',
            'events' => [WebhookEvent::POST_CREATED],
            'secret' => 'mysecret1234567890123456789012',
        ]);

        $this->consoleBlogApi('GET', 'wh-test', '/webhooks', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame('https://example.com/hook', $json[0]['url']);
        $this->assertSame(['post.created'], $json[0]['events']);
        $this->assertArrayHasKey('secret', $json[0]);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-denied'],
            ['status' => 'active'],
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
