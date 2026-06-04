<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\AuthorsController;
use App\Entity\Enum\BlogHostingAt;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AuthorsController::class)]
class AuthorsTest extends ApiTestCase
{
    private $blog;
    private $lang1;
    private $lang2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $this->lang1 = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'en', 'is_primary' => true]);
        $this->lang2 = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'fr', 'is_primary' => false]);
        RouteFactory::createOne(['blog' => $this->blog, 'name' => 'author', 'match' => '/author/{slug}', 'template' => 'author', 'is_enabled' => true]);
    }

    private function createAuthors(int $count, array $attrs = []): array
    {
        $authors = [];
        for ($i = 0; $i < $count; $i++) {
            $author = UserFactory::createOne(array_merge([
                'blog' => $this->blog,
                'posts_count' => rand(1, 100),
                'status' => 'active',
            ], $attrs));
            UserVariantFactory::createOne([
                'user' => $author,
                'user_id' => $author->getId(),
                'language_id' => $this->lang1->getId(),
                'language' => $this->lang1,
            ]);
            $authors[] = $author;
        }
        return $authors;
    }

    public function test_fetches_authors_without_params(): void
    {
        $this->createAuthors(4);

        $this->dataApi($this->blog, '/authors');

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(4, $json['data']);
        $this->assertArrayHasKey('pagination', $json);
        $this->assertSame('en', $json['data'][0]['language']['code']);
    }

    public function test_works_with_language(): void
    {
        $authors = $this->createAuthors(2);
        foreach ($authors as $author) {
            UserVariantFactory::createOne([
                'user' => $author,
                'user_id' => $author->getId(),
                'language_id' => $this->lang2->getId(),
                'language' => $this->lang2,
            ]);
        }

        $this->dataApi($this->blog, '/authors', ['language' => 'fr']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(2, $json['data']);
        $this->assertSame('fr', $json['data'][0]['language']['code']);
    }

    public function test_does_not_work_with_wrong_language(): void
    {
        $this->dataApi($this->blog, '/authors', ['language' => 'jp']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_gives_correct_limit(): void
    {
        $this->createAuthors(2);

        $this->dataApi($this->blog, '/authors', ['limit' => 1]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
    }

    public function test_gives_correct_page_for_pagination(): void
    {
        $em = $this->getEm();

        $author1 = UserFactory::createOne(['blog' => $this->blog, 'posts_count' => 101, 'status' => 'active']);
        $author2 = UserFactory::createOne(['blog' => $this->blog, 'posts_count' => 100, 'status' => 'active']);
        UserVariantFactory::createOne(['user' => $author1, 'user_id' => $author1->getId(), 'language_id' => $this->lang1->getId(), 'language' => $this->lang1]);
        UserVariantFactory::createOne(['user' => $author2, 'user_id' => $author2->getId(), 'language_id' => $this->lang1->getId(), 'language' => $this->lang1]);

        $this->dataApi($this->blog, '/authors', ['limit' => 1, 'page' => 2]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame($author2->getId(), $json['data'][0]['id']);
    }

    public function test_does_not_work_for_invalid_limit(): void
    {
        $this->dataApi($this->blog, '/authors', ['limit' => 0]);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_does_not_work_for_invalid_page(): void
    {
        $this->dataApi($this->blog, '/authors', ['page' => -1]);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_does_not_work_for_invalid_sort(): void
    {
        $this->dataApi($this->blog, '/authors', ['sort' => 'something_invalid']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_does_not_work_for_invalid_sort_method(): void
    {
        $this->dataApi($this->blog, '/authors', ['sort' => 'published_at SOME']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_sorts_by_posts_count_desc(): void
    {
        $authors = $this->createAuthors(3);
        $em = $this->getEm();
        foreach ($authors as $i => $a) {
            $u = $em->find(\App\Entity\User::class, $a->getId());
            $u->setPostsCount(($i + 1) * 10);
        }
        $em->flush();

        $this->dataApi($this->blog, '/authors', ['sort' => 'posts_count', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertGreaterThanOrEqual($json['data'][1]['posts_count'], $json['data'][0]['posts_count']);
        $this->assertGreaterThanOrEqual($json['data'][2]['posts_count'], $json['data'][1]['posts_count']);
    }

    public function test_sorts_by_posts_count_asc(): void
    {
        $authors = $this->createAuthors(3);
        $em = $this->getEm();
        foreach ($authors as $i => $a) {
            $u = $em->find(\App\Entity\User::class, $a->getId());
            $u->setPostsCount(($i + 1) * 10);
        }
        $em->flush();

        $this->dataApi($this->blog, '/authors', ['sort' => 'posts_count ASC', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertLessThanOrEqual($json['data'][1]['posts_count'], $json['data'][0]['posts_count']);
        $this->assertLessThanOrEqual($json['data'][2]['posts_count'], $json['data'][1]['posts_count']);
    }

    public function test_filters_keys(): void
    {
        $this->createAuthors(3);

        $this->dataApi($this->blog, '/authors', ['keys' => 'id', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json['data']);
        $this->assertArrayHasKey('id', $json['data'][0]);
        $this->assertArrayNotHasKey('slug', $json['data'][0]);
    }

    public function test_filters_by_id(): void
    {
        $authors = $this->createAuthors(2);

        $this->dataApi($this->blog, '/authors', ['filter' => 'id=' . $authors[0]->getId()]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame($authors[0]->getId(), $json['data'][0]['id']);
    }

    public function test_filters_by_slug(): void
    {
        $author = UserFactory::createOne(['blog' => $this->blog, 'posts_count' => 5, 'slug' => 'filter-by-slug-test', 'status' => 'active']);
        UserVariantFactory::createOne(['user' => $author, 'user_id' => $author->getId(), 'language_id' => $this->lang1->getId(), 'language' => $this->lang1]);

        $this->dataApi($this->blog, '/authors', ['filter' => "slug='filter-by-slug-test'"]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame('filter-by-slug-test', $json['data'][0]['slug']);
    }

    public function test_sends_total_correctly(): void
    {
        $this->createAuthors(3);

        $this->dataApi($this->blog, '/authors');

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame(3, $json['pagination']['total']);
    }
}
