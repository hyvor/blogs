<?php

namespace App\Tests\Api\Console\Blog\Tag;

use App\Api\Console\Controller\TagController;
use App\Api\Console\Object\TagObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Service\Tag\TagService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\TagFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagController::class)]
#[CoversClass(TagService::class)]
#[CoversClass(TagObject::class)]
#[CoversClass(TagObjectFactory::class)]
class GetTagsTest extends ApiTestCase
{
    public function test_fetches_tags(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'tags-get'],
            ['status' => 'active'],
        );

        for ($i = 0; $i < 4; $i++) {
            TagFactory::createOne(['blog' => $blog]);
        }

        TagFactory::createOne();

        $this->consoleBlogApi('GET', $blog, '/tags?limit=2', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(2, $json);
        $this->assertArrayHasKey('id', $json[0]);
        $this->assertArrayHasKey('slug', $json[0]);
    }

    public function test_fetches_tags_with_offset(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'tags-get-offset'],
            ['status' => 'active'],
        );

        for ($i = 0; $i < 2; $i++) {
            TagFactory::createOne(['blog' => $blog]);
        }

        $this->consoleBlogApi('GET', $blog, '/tags?limit=1&offset=1', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
    }
}
