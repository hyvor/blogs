<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\AuthorsController;
use App\Api\Data\Factory\AuthorObjectFactory;
use App\Entity\Enum\BlogHostingAt;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AuthorsController::class)]
#[CoversClass(AuthorObjectFactory::class)]
class AuthorTest extends ApiTestCase
{
    private $blog;
    private $lang1;
    private $lang2;
    private $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $this->lang1 = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'en', 'is_primary' => true]);
        $this->lang2 = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'fr', 'is_primary' => false]);
        RouteFactory::createOne(['blog' => $this->blog, 'name' => 'author', 'match' => '/author/{slug}', 'template' => 'author', 'is_enabled' => true]);

        $this->author = UserFactory::createOne([
            'blog' => $this->blog,
            'slug' => 'john-doe',
            'posts_count' => 2,
            'status' => 'active',
        ]);

        UserVariantFactory::createOne([
            'user' => $this->author,
            'user_id' => $this->author->getId(),
            'language_id' => $this->lang1->getId(),
            'language' => $this->lang1,
            'name' => 'John Doe',
        ]);

        UserVariantFactory::createOne([
            'user' => $this->author,
            'user_id' => $this->author->getId(),
            'language_id' => $this->lang2->getId(),
            'language' => $this->lang2,
            'name' => 'Jean Dupont',
        ]);
    }

    public function test_fetches_author_by_id(): void
    {
        $this->dataApi($this->blog, '/author', ['id' => $this->author->getId()]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($this->author->getId(), $json['id']);
        $this->assertSame('john-doe', $json['slug']);
    }

    public function test_fetches_author_by_slug(): void
    {
        $this->dataApi($this->blog, '/author', ['slug' => 'john-doe']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($this->author->getId(), $json['id']);
    }

    public function test_does_not_fetch_user_when_posts_count_is_zero(): void
    {
        $em = $this->getEm();
        $user = $em->find(\App\Entity\User::class, $this->author->getId());
        $user->setPostsCount(0);
        $em->flush();

        $this->dataApi($this->blog, '/author', ['id' => $this->author->getId()]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_validates_id(): void
    {
        $this->dataApi($this->blog, '/author', ['id' => 'oh, hi!']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_fetches_author_by_id_and_language(): void
    {
        $this->dataApi($this->blog, '/author', ['id' => $this->author->getId(), 'language' => 'fr']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('fr', $json['language']['code']);
    }

    public function test_requires_valid_language(): void
    {
        $this->dataApi($this->blog, '/author', ['id' => $this->author->getId(), 'language' => 'jp']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_returns_404_if_author_not_found(): void
    {
        $this->dataApi($this->blog, '/author', ['id' => 999999]);
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_filters_keys(): void
    {
        $this->dataApi($this->blog, '/author', ['id' => $this->author->getId(), 'keys' => 'id']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayNotHasKey('slug', $json);
    }
}
