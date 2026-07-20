<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorPost;

use App\Api\Console\Controller\HyvorPostController;
use App\Entity\HyvorPost;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HyvorPostController::class)]
#[CoversClass(HyvorPostService::class)]
class DisconnectHyvorPostIntegrationTest extends ApiTestCase
{
    public function test_disconnects_and_removes_the_row(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-disconnect-ok']);
        HyvorPostFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-post/disconnect', user: $owner);

        $this->assertResponseIsSuccessful();
        $hyvorPost = $this->getEm()->getRepository(HyvorPost::class)->findOneBy(['blog' => $blog]);
        $this->assertNull($hyvorPost);
    }

    public function test_404_when_not_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-disconnect-missing']);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-post/disconnect', user: $owner);

        $this->assertResponseStatusCodeSame(404);
    }
}
