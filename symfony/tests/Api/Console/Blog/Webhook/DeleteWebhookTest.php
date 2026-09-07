<?php

namespace App\Tests\Api\Console\Blog\Webhook;

use App\Api\Console\Controller\WebhookController;
use App\Entity\Enum\UserStatus;
use App\Entity\Webhook;
use App\Service\Webhook\WebhookService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\WebhookFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookController::class)]
#[CoversClass(WebhookService::class)]
class DeleteWebhookTest extends ApiTestCase
{
    public function test_delete_webhook(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'wh-delete'],
            ['status' => UserStatus::ACTIVE],
        );
        $webhook = WebhookFactory::createOne([
            'blog' => $blog,
        ]);

        $this->consoleBlogApi('DELETE', 'wh-delete', '/webhook/' . $webhook->getId(), user: $user);

        $this->assertResponseIsSuccessful();

        $webhooks = $this->getEm()->getRepository(Webhook::class)->findAll();
        $this->assertCount(0, $webhooks);
    }
}
