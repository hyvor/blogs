<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\TagsController;
use App\Api\Data\Factory\TagObjectFactory;
use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Language;
use App\Entity\Tag;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagsController::class)]
#[CoversClass(TagObjectFactory::class)]
class TagTest extends ApiTestCase
{
    private Blog $blog;
    private Language $lang1;
    private Language $lang2;
    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $this->lang1 = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'en', 'is_primary' => true]);
        $this->lang2 = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'fr', 'is_primary' => false]);
        RouteFactory::createOne(['blog' => $this->blog, 'name' => 'tag', 'match' => '/tag/{slug}', 'template' => 'tag', 'is_enabled' => true]);

        $this->tag = TagFactory::createOne([
            'blog' => $this->blog,
            'slug' => 'my-tag',
            'is_private' => false,
        ]);

        TagVariantFactory::createOne([
            'tag' => $this->tag,
            'language' => $this->lang1,
            'name' => 'My Tag',
        ]);

        TagVariantFactory::createOne([
            'tag' => $this->tag,
            'language' => $this->lang2,
            'name' => 'Mon Tag',
        ]);
    }

    public function test_fetches_tag_by_id(): void
    {
        $this->dataApi($this->blog, '/tag', ['id' => $this->tag->getId()]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($this->tag->getId(), $json['id']);
        $this->assertSame('my-tag', $json['slug']);
    }

    public function test_fetches_tag_by_slug(): void
    {
        $this->dataApi($this->blog, '/tag', ['slug' => 'my-tag']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($this->tag->getId(), $json['id']);
    }

    public function test_validates_id(): void
    {
        $this->dataApi($this->blog, '/tag', ['id' => 'oh, hi!']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_fetches_tag_by_id_and_language(): void
    {
        $this->dataApi($this->blog, '/tag', ['id' => $this->tag->getId(), 'language' => 'fr']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['language']);
        $this->assertSame('fr', $json['language']['code']);
        $this->assertSame('Mon Tag', $json['name']);
    }

    public function test_returns_404_if_tag_not_found(): void
    {
        $this->dataApi($this->blog, '/tag', ['id' => 999999]);
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_returns_404_if_tag_not_found_by_slug(): void
    {
        $this->dataApi($this->blog, '/tag', ['slug' => 'non-existent-slug']);
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_filters_keys(): void
    {
        $this->dataApi($this->blog, '/tag', ['id' => $this->tag->getId(), 'keys' => 'id']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayNotHasKey('slug', $json);
    }
}
