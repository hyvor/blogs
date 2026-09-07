<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Api\Console\Object\WebhookObject;
use App\Entity\Enum\UserStatus;
use App\Entity\Enum\WebhookEvent;
use App\Service\Webhook\WebhookService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookFactory;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(WebhookController::class)]
#[CoversClass(WebhookObject::class)]
#[CoversClass(WebhookService::class)]
class UpdateWebhookTest extends ApiTestCase
{
    public function test_update_webhook(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-update'],
            ['status' => UserStatus::ACTIVE],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
            'url' => 'https://old.com/hook',
            'events' => [WebhookEvent::POST_CREATED],
        ]);

        $this->consoleBlogApi('PATCH', 'wh-update', '/webhook/' . $webhook->getId(), [
            'url' => 'https://new.com/hook',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('https://new.com/hook', $json['url']);

        refresh($webhook);
        $this->assertSame('https://new.com/hook', $webhook->getUrl());
        $this->assertSame([WebhookEvent::POST_CREATED], $webhook->getEvents());
    }

    public function test_fails_on_invalid_event(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-update-2'],
            ['status' => UserStatus::ACTIVE],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
            'url' => 'https://old.com/hook',
            'events' => [WebhookEvent::POST_CREATED],
        ]);

        $this->consoleBlogApi('PATCH', 'wh-update-2', '/webhook/' . $webhook->getId(), [
            'events' => ['invalid.event'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_update_webhook_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-upd-b1'],
            ['status' => UserStatus::ACTIVE],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-upd-b2'],
            ['status' => UserStatus::ACTIVE],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog2,
        ]);

        $this->consoleBlogApi('PATCH', 'wh-upd-b1', '/webhook/' . $webhook->getId(), [
            'url' => 'https://hack.com/hook',
        ], user: $user1);

        $this->assertResponseStatusCodeSame(404);
    }
}
