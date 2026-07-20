<?php

namespace App\Tests\Api\Console\Blog\Integrations\HyvorPost;

use App\Api\Console\Controller\HyvorPostController;
use App\Entity\HyvorPost;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use App\Tests\Fake\HyvorPostServiceFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HyvorPostController::class)]
#[CoversClass(HyvorPostService::class)]
class ConnectHyvorPostIntegrationTest extends ApiTestCase
{
    public function test_connects_and_persists_the_integration(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-connect-ok']);

        $fake = new HyvorPostServiceFake($this->getEm());
        $this->getContainer()->set(HyvorPostService::class, $fake);

        $response = $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-post/connect', [
            'name' => 'My Newsletter',
            'subdomain' => 'my-newsletter',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('My Newsletter', $fake->lastConnectName);
        $this->assertSame('my-newsletter', $fake->lastConnectSubdomain);

        $hyvorPost = $this->getEm()->getRepository(HyvorPost::class)->findOneBy(['blog' => $blog]);
        $this->assertNotNull($hyvorPost);
        $this->assertTrue($hyvorPost->isCreatedByBlogs());
    }

    public function test_conflicts_when_already_connected(): void
    {
        [$blog, $owner] = BlogFactory::createOneWithUser(['subdomain' => 'hp-connect-conflict']);
        HyvorPostFactory::createOne(['blog' => $blog]);

        $fake = new HyvorPostServiceFake($this->getEm());
        $this->getContainer()->set(HyvorPostService::class, $fake);

        $this->consoleBlogApi('POST', $blog, '/integrations/hyvor-post/connect', [
            'name' => 'My Newsletter',
            'subdomain' => 'my-newsletter',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
    }
}
