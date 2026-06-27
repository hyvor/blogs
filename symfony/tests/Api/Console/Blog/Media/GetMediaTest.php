<?php

namespace App\Tests\Api\Console\Blog\Media;

use App\Api\Console\Controller\MediaController;
use App\Api\Console\Object\MediaObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Service\Media\MediaService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MediaController::class)]
#[CoversClass(MediaService::class)]
#[CoversClass(MediaObjectFactory::class)]
class GetMediaTest extends ApiTestCase
{
    public function test_gets_media(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'get-media']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        MediaFactory::createMany(3, ['blog' => $blog]);

        $this->consoleBlogApi('GET', $blog, '/media', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json);
        foreach ($json as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('original_name', $item);
            $this->assertArrayHasKey('url', $item);
        }
    }

    public function test_limit_and_offset_orders_by_id_desc(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'get-media-limit']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $mediaList = MediaFactory::createMany(3, ['blog' => $blog]);

        $firstMedia = $mediaList[0];

        $this->consoleBlogApi('GET', $blog, '/media?limit=1&offset=2', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame($firstMedia->getId(), $json[0]['id']);
    }

    public function test_filters_by_extensions(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'get-media-ext']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        MediaFactory::createMany(2, ['blog' => $blog, 'extension' => 'jpg']);
        MediaFactory::createOne(['blog' => $blog, 'extension' => 'svg']);

        $this->consoleBlogApi('GET', $blog, '/media?extensions[]=svg', user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $this->getJson());
    }

    public function test_filters_by_image_type(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'get-media-type']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        MediaFactory::createOne(['blog' => $blog, 'extension' => 'pdf']);
        MediaFactory::createOne(['blog' => $blog, 'extension' => 'svg']);
        MediaFactory::createOne(['blog' => $blog, 'extension' => 'jpg']);

        $this->consoleBlogApi('GET', $blog, '/media?type=image', user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $this->getJson());
    }

    public function test_searches_media(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'get-media-search']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        MediaFactory::createOne(['blog' => $blog, 'name' => 'test', 'original_name' => 'test']);
        MediaFactory::createOne(['blog' => $blog, 'name' => 'unrelated', 'original_name' => 'testing']);
        MediaFactory::createOne(['blog' => $blog, 'name' => 'old_test', 'original_name' => 'old_test']);
        MediaFactory::createOne(['blog' => $blog, 'name' => 'unrelated2', 'original_name' => 'unrelated2']);

        $this->consoleBlogApi('GET', $blog, '/media?search=test', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json);
    }
}
