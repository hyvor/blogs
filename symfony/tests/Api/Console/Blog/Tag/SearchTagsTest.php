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
class SearchTagsTest extends ApiTestCase
{
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

        $this->consoleBlogApi('GET', $blog, '/tags/search?search=Thisis', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertIsArray($json[0]['variants']);
        $this->assertIsArray($json[0]['variants'][0]);
        $this->assertSame($name, $json[0]['variants'][0]['name']);
    }
}
