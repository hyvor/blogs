<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Factory\PostObjectFactory;
use App\Api\Data\KeysFilter;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\PostVariantStatus;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostTagFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(KeysFilter::class)]
class KeysTest extends ApiTestCase
{
    private $postObject;

    protected function setUp(): void
    {
        parent::setUp();

        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $lang = LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'is_enabled' => true]);

        $post = PostFactory::createOne([
            'blog' => $blog,
            'is_page' => false,
            'published_at' => new \DateTimeImmutable(),
        ]);

        PostVariantFactory::createOne([
            'post' => $post,
            'post_id' => $post->getId(),
            'language' => $lang,
            'language_id' => $lang->getId(),
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'keys-test-' . $post->getId(),
        ]);

        $tag = TagFactory::createOne(['blog' => $blog, 'slug' => 'keys-tag', 'is_private' => false]);
        PostTagFactory::createOne(['post' => $post, 'post_id' => $post->getId(), 'tag' => $tag, 'tag_id' => $tag->getId()]);

        /** @var PostObjectFactory $factory */
        $factory = $this->getContainer()->get(PostObjectFactory::class);
        $this->postObject = $factory->createFromEntity($post, $blog, $lang);
    }

    private function j(mixed $obj): array
    {
        return json_decode((string)json_encode($obj), true);
    }

    public function test_does_not_filter_keys_when_null(): void
    {
        $obj = KeysFilter::filter($this->postObject, null);
        $this->assertEquals($this->j($this->postObject), $this->j($obj));
    }

    public function test_filters_basic_key(): void
    {
        $arr = $this->j(KeysFilter::filter($this->postObject, 'id'));

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayNotHasKey('slug', $arr);
    }

    public function test_filters_multiple_keys(): void
    {
        $arr = $this->j(KeysFilter::filter($this->postObject, 'id, slug'));

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('slug', $arr);
        $this->assertArrayNotHasKey('url', $arr);
    }

    public function test_filters_nested_object_keys(): void
    {
        $arr = $this->j(KeysFilter::filter($this->postObject, 'tags.id'));
        $this->assertNotEmpty($arr['tags']);
        $tag = $arr['tags'][0];

        $this->assertArrayHasKey('id', $tag);
        $this->assertArrayNotHasKey('slug', $tag);
    }

    public function test_filters_nested_object_multi_keys(): void
    {
        $arr = $this->j(KeysFilter::filter($this->postObject, 'tags.id, tags.slug'));
        $this->assertNotEmpty($arr['tags']);
        $tag = $arr['tags'][0];

        $this->assertArrayHasKey('id', $tag);
        $this->assertArrayHasKey('slug', $tag);
        $this->assertArrayNotHasKey('url', $tag);
    }

    public function test_filters_nested_objects_given_object_name(): void
    {
        $arr = $this->j(KeysFilter::filter($this->postObject, 'tags'));

        $this->assertArrayHasKey('tags', $arr);
        $this->assertNotEmpty($arr['tags']);
        $tag = $arr['tags'][0];

        $this->assertArrayHasKey('id', $tag);
        $this->assertArrayHasKey('slug', $tag);
    }

    public function test_filters_keys_with_exclude(): void
    {
        $arr = $this->j(KeysFilter::filter($this->postObject, '!id'));

        $this->assertArrayNotHasKey('id', $arr);
        $this->assertArrayHasKey('slug', $arr);
    }

    public function test_filters_keys_with_nested_exclude(): void
    {
        $arr = $this->j(KeysFilter::filter($this->postObject, '!tags.id'));

        $this->assertNotEmpty($arr['tags']);
        $tag = $arr['tags'][0];

        $this->assertArrayNotHasKey('id', $tag);
        $this->assertArrayHasKey('slug', $tag);
    }
}
