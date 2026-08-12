<?php

namespace App\Tests\Api\Console\Blog\Tag;

use App\Api\Console\Controller\TagController;
use App\Api\Console\Object\TagObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Api\Console\Object\TagVariantObject;
use App\Api\Console\Object\TagVariantObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Service\Tag\TagService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagController::class)]
#[CoversClass(TagService::class)]
#[CoversClass(TagObject::class)]
#[CoversClass(TagObjectFactory::class)]
#[CoversClass(TagVariantObject::class)]
#[CoversClass(TagVariantObjectFactory::class)]
class GetTagsTest extends ApiTestCase
{
    public function test_fetches_tags(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'tags-get'],
            ['status' => UserStatus::ACTIVE],
        );

        for ($i = 0; $i < 4; $i++) {
            TagFactory::createOne(['blog' => $blog]);
        }

        TagFactory::createOne();

        $this->consoleBlogApi('GET', $blog, '/tags?limit=2', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(2, $json);
        $this->assertIsArray($json[0]);
        $this->assertArrayHasKey('id', $json[0]);
        $this->assertArrayHasKey('slug', $json[0]);
    }

    public function test_fetches_tags_with_offset(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'tags-get-offset'],
            ['status' => UserStatus::ACTIVE],
        );

        for ($i = 0; $i < 2; $i++) {
            TagFactory::createOne(['blog' => $blog]);
        }

        $this->consoleBlogApi('GET', $blog, '/tags?limit=1&offset=1', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
    }

    public function test_searches_tags(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tags-search']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $name = 'Thisisname';
        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $language, 'name' => $name]);

        // another tag
        $tag2 = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag2, 'language' => $language, 'name' => 'Another name']);

        $this->consoleBlogApi('GET', $blog, '/tags?search=Thisis', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertIsArray($json[0]['variants']);
        $this->assertIsArray($json[0]['variants'][0]);
        $this->assertSame($name, $json[0]['variants'][0]['name']);
    }

    public function test_search_keeps_all_variants_of_matched_tag(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tags-search-variants']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $primaryLanguage = LanguageFactory::createOnePrimaryFor($blog);
        $secondaryLanguage = LanguageFactory::createOneFor($blog, ['code' => 'fr', 'name' => 'French']);

        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $primaryLanguage, 'name' => 'Matching name']);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $secondaryLanguage, 'name' => 'Nom francais']);

        $this->consoleBlogApi('GET', $blog, '/tags?search=Matching', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertIsArray($json[0]['variants']);
        $this->assertCount(2, $json[0]['variants']);
    }
}
