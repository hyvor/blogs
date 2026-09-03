<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Api\Console\Object\HostingChangeObject;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\Enum\UserStatus;
use App\Service\Hosting\HostingChangeService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HostingChangeFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(HostingChangeObject::class)]
#[CoversClass(HostingChangeService::class)]
class GetHostingHistoryTest extends ApiTestCase
{
    public function test_returns_empty_history(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-history-empty'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('GET', $blog, '/hosting/history', user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame([], $this->getJson());
    }

    public function test_returns_history_including_pending_and_completed_changes(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-history'],
            ['status' => UserStatus::ACTIVE],
        );

        HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SUBDOMAIN,
            'to_at' => BlogHostingAt::SELF,
            'status' => HostingChangeStatus::FAILED,
            'error_message' => 'Something went wrong',
        ]);
        HostingChangeFactory::createOne([
            'blog' => $blog,
            'from_at' => BlogHostingAt::SELF,
            'to_at' => BlogHostingAt::SUBDOMAIN,
            'status' => HostingChangeStatus::CHANGING,
        ]);

        $this->consoleBlogApi('GET', $blog, '/hosting/history', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(2, $json);
        $this->assertIsArray($json[0]);
        $this->assertIsArray($json[1]);

        // most recent first
        $this->assertSame('changing', $json[0]['status']);
        $this->assertSame('failed', $json[1]['status']);
        // error_message is internal only and must never be exposed to the client
        $this->assertArrayNotHasKey('error_message', $json[1]);
    }

    public function test_does_not_return_other_blogs_history(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-history-own'],
            ['status' => UserStatus::ACTIVE],
        );
        $otherBlog = BlogFactory::createOne(['subdomain' => 'hosting-history-other']);

        HostingChangeFactory::createOne([
            'blog' => $otherBlog,
            'status' => HostingChangeStatus::SUCCESS,
        ]);

        $this->consoleBlogApi('GET', $blog, '/hosting/history', user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame([], $this->getJson());
    }
}
