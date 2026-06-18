<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\TagsController;
use App\Entity\Enum\BlogHostingAt;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagsController::class)]
class TagsTest extends ApiTestCase
{
    private $blog;
    private $lang1;
    private $lang2;
    /** @var \App\Entity\Tag[] */
    private array $tags = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $this->lang1 = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'en', 'is_primary' => true]);
        $this->lang2 = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'fr', 'is_primary' => false]);
        RouteFactory::createOne(['blog' => $this->blog, 'name' => 'tag', 'match' => '/tag/{slug}', 'template' => 'tag', 'is_enabled' => true]);

        for ($i = 0; $i < 4; $i++) {
            $tag = TagFactory::createOne([
                'blog' => $this->blog,
                'slug' => 'tag-' . $i . '-' . uniqid(),
                'is_private' => false,
                'posts_count' => $i,
            ]);
            TagVariantFactory::createOne([
                'tag' => $tag,
                'language' => $this->lang1,
            ]);
            TagVariantFactory::createOne([
                'tag' => $tag,
                'language' => $this->lang2,
            ]);
            $this->tags[] = $tag;
        }

        // other blog
        TagFactory::createOne();
    }

    public function test_fetches_tags_without_params(): void
    {
        $this->dataApi($this->blog, '/tags');

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(4, $json['data']);
        $this->assertArrayHasKey('pagination', $json);
        $this->assertSame('en', $json['data'][0]['language']['code']);
    }

    public function test_works_with_language(): void
    {
        $this->dataApi($this->blog, '/tags', ['language' => 'fr']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(4, $json['data']);
        $this->assertSame('fr', $json['data'][0]['language']['code']);
    }

    public function test_does_not_work_with_wrong_language(): void
    {
        $this->dataApi($this->blog, '/tags', ['language' => 'jp']);
        $this->assertResponseFailed(422, 'Language not found');
    }

    public function test_gives_correct_limit(): void
    {
        $this->dataApi($this->blog, '/tags', ['limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json['data']);
    }

    public function test_gives_correct_page_for_pagination(): void
    {
        // default order is posts_count DESC
        // Update posts_count to control sort order
        $em = $this->getEm();
        $tag0 = $em->find(\App\Entity\Tag::class, $this->tags[0]->getId());
        $tag1 = $em->find(\App\Entity\Tag::class, $this->tags[1]->getId());
        $tag0->setPostsCount(101);
        $tag1->setPostsCount(100);
        $em->flush();

        $this->dataApi($this->blog, '/tags', ['limit' => 1, 'page' => 2]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame($this->tags[1]->getId(), $json['data'][0]['id']);
    }

    public function test_does_not_work_for_invalid_limit(): void
    {
        $this->dataApi($this->blog, '/tags', ['limit' => 0]);
        $this->assertResponseFailed(422, 'limit: This value should be greater than or equal to 1');
    }

    public function test_max_limit_is_250(): void
    {
        $this->dataApi($this->blog, '/tags', ['limit' => 251]);
        $this->assertResponseFailed(422, 'limit: This value should be less than or equal to 250');
    }

    public function test_does_not_work_for_invalid_page(): void
    {
        $this->dataApi($this->blog, '/tags', ['page' => -1]);
        $this->assertResponseFailed(422, 'page: This value should be greater than or equal to 1');
    }

    public function test_does_not_work_for_invalid_sort(): void
    {
        $this->dataApi($this->blog, '/tags', ['sort' => 'something_invalid']);
        $this->assertResponseFailed(422, 'Sort by something_invalid not supported');
    }

    public function test_does_not_work_for_invalid_sort_method(): void
    {
        $this->dataApi($this->blog, '/tags', ['sort' => 'posts_count SOME']);
        $this->assertResponseFailed(422, 'Sort method SOME not supported');
    }

    public function test_sorts_by_posts_count_desc(): void
    {
        $em = $this->getEm();
        foreach ($this->tags as $i => $t) {
            $tag = $em->find(\App\Entity\Tag::class, $t->getId());
            $tag->setPostsCount(($i + 1) * 10);
        }
        $em->flush();

        $this->dataApi($this->blog, '/tags', ['sort' => 'posts_count', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertGreaterThanOrEqual($json['data'][1]['posts_count'], $json['data'][0]['posts_count']);
        $this->assertGreaterThanOrEqual($json['data'][2]['posts_count'], $json['data'][1]['posts_count']);
    }

    public function test_sorts_by_posts_count_asc(): void
    {
        $em = $this->getEm();
        foreach ($this->tags as $i => $t) {
            $tag = $em->find(\App\Entity\Tag::class, $t->getId());
            $tag->setPostsCount(($i + 1) * 10);
        }
        $em->flush();

        $this->dataApi($this->blog, '/tags', ['sort' => 'posts_count ASC', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertLessThanOrEqual($json['data'][1]['posts_count'], $json['data'][0]['posts_count']);
        $this->assertLessThanOrEqual($json['data'][2]['posts_count'], $json['data'][1]['posts_count']);
    }

    public function test_filters_keys(): void
    {
        $this->dataApi($this->blog, '/tags', ['keys' => 'id', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json['data']);
        $this->assertArrayHasKey('id', $json['data'][0]);
        $this->assertArrayNotHasKey('slug', $json['data'][0]);
    }

    public function test_filters_by_id(): void
    {
        $tag = $this->tags[0];

        $this->dataApi($this->blog, '/tags', ['filter' => 'id=' . $tag->getId()]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame($tag->getId(), $json['data'][0]['id']);
    }

    public function test_filters_by_slug(): void
    {
        $tag = $this->tags[1];

        $this->dataApi($this->blog, '/tags', ['filter' => "slug='{$tag->getSlug()}'"]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame($tag->getSlug(), $json['data'][0]['slug']);
    }

    public function test_filters_by_posts_count(): void
    {
        $em = $this->getEm();
        foreach ($this->tags as $i => $t) {
            $tag = $em->find(\App\Entity\Tag::class, $t->getId());
            $tag->setPostsCount(($i + 1) * 10);
        }
        $em->flush();

        $this->dataApi($this->blog, '/tags', ['filter' => 'posts_count>=30']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(2, $json['data']);
        foreach ($json['data'] as $item) {
            $this->assertGreaterThanOrEqual(30, $item['posts_count']);
        }
    }

    public function test_filters_by_created_at(): void
    {
        $em = $this->getEm();
        foreach ($this->tags as $i => $tag) {
            $createdAt = new \DateTimeImmutable();
            $createdAt = $createdAt->modify("-{$i} days");
            $tag->setCreatedAt($createdAt);
        }
        $em->flush();

        $date = (new \DateTimeImmutable())->modify('-2 days')->format('Y-m-d');
        $this->dataApi($this->blog, '/tags', ['filter' => "created_at>='{$date}'"]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json['data']);
        foreach ($json['data'] as $item) {
            $itemCreatedAt = new DateTimeImmutable('@' . $item['created_at'])->format('Y-m-d');
            $this->assertGreaterThanOrEqual($date, $itemCreatedAt);
        }
    }

    public function test_sends_total_correctly(): void
    {
        $this->dataApi($this->blog, '/tags', ['limit' => 2]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame(4, $json['pagination']['total']);
    }

    public function test_visibility_public_default(): void
    {
        // Add a private tag
        $privateTag = TagFactory::createOne([
            'blog' => $this->blog,
            'slug' => 'private-tag-' . uniqid(),
            'is_private' => true,
        ]);

        $this->dataApi($this->blog, '/tags');

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(4, $json['data']); // only public tags
        $this->assertFalse($json['data'][0]['is_private']);
    }

    public function test_visibility_private(): void
    {
        $privateTag = TagFactory::createOne([
            'blog' => $this->blog,
            'slug' => 'private-only-' . uniqid(),
            'is_private' => true,
        ]);
        TagVariantFactory::createOne([
            'tag' => $privateTag,
            'language' => $this->lang1,
        ]);

        $this->dataApi($this->blog, '/tags', ['visibility' => 'private']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertTrue($json['data'][0]['is_private']);
        $this->assertSame($privateTag->getId(), $json['data'][0]['id']);
    }

    public function test_visibility_any(): void
    {
        $privateTag = TagFactory::createOne([
            'blog' => $this->blog,
            'slug' => 'any-private-' . uniqid(),
            'is_private' => true,
        ]);

        $this->dataApi($this->blog, '/tags', ['visibility' => 'any']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(5, $json['data']); // 4 public + 1 private
    }

    public function test_filter_error(): void
    {
        $this->dataApi($this->blog, '/tags', ['filter' => 'posts_count=test']);
        $this->assertResponseFailed(422, 'Filter error: Value for posts_count should be one of: int');
    }
}
