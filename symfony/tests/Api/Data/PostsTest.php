<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\PostsController;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\PostVariantStatus;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostAuthorFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostTagFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostsController::class)]
class PostsTest extends ApiTestCase
{
    /** @var \App\Entity\Blog */
    private $blog;
    /** @var \App\Entity\Language */
    private $primaryLanguage;
    /** @var \App\Entity\Language */
    private $secondaryLanguage;
    /** @var \App\Entity\Post[] */
    private array $posts = [];
    /** @var \App\Entity\Post[] */
    private array $pages = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $this->primaryLanguage = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'en', 'is_primary' => true]);
        $this->secondaryLanguage = LanguageFactory::createOne(['blog' => $this->blog, 'code' => 'fr', 'is_primary' => false]);
        RouteFactory::createOne(['blog' => $this->blog, 'name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'is_enabled' => true]);

        // Create 4 posts
        for ($i = 0; $i < 4; $i++) {
            $post = PostFactory::createOne([
                'blog' => $this->blog,
                'is_page' => false,
                'is_featured' => false,
                'published_at' => new \DateTimeImmutable('-' . $i . ' days'),
            ]);
            PostVariantFactory::createOne([
                'post' => $post,
                'post_id' => $post->getId(),
                'language' => $this->primaryLanguage,
                'language_id' => $this->primaryLanguage->getId(),
                'status' => PostVariantStatus::PUBLISHED,
                'slug' => 'post-' . $i . '-' . $post->getId(),
                'title' => 'Post ' . $i,
                'words' => $i * 10,
            ]);
            PostVariantFactory::createOne([
                'post' => $post,
                'post_id' => $post->getId(),
                'language' => $this->secondaryLanguage,
                'language_id' => $this->secondaryLanguage->getId(),
                'status' => PostVariantStatus::PUBLISHED,
                'slug' => 'post-fr-' . $i . '-' . $post->getId(),
                'title' => 'Post FR ' . $i,
            ]);
            $this->posts[] = $post;
        }

        // Create 3 pages
        for ($i = 0; $i < 3; $i++) {
            $page = PostFactory::createOne([
                'blog' => $this->blog,
                'is_page' => true,
                'is_featured' => false,
                'published_at' => new \DateTimeImmutable('-' . $i . ' days'),
            ]);
            PostVariantFactory::createOne([
                'post' => $page,
                'post_id' => $page->getId(),
                'language' => $this->primaryLanguage,
                'language_id' => $this->primaryLanguage->getId(),
                'status' => PostVariantStatus::PUBLISHED,
                'slug' => 'page-' . $i . '-' . $page->getId(),
                'title' => 'Page ' . $i,
            ]);
            $this->pages[] = $page;
        }
    }

    public function test_fetches_posts_without_params(): void
    {
        $this->dataApi($this->blog, '/posts');

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(4, $json['data']);
        $this->assertArrayHasKey('pagination', $json);
        $this->assertSame('en', $json['data'][0]['language']['code']);
    }

    public function test_fetches_pages(): void
    {
        $this->dataApi($this->blog, '/posts', ['limit' => 2, 'pages' => 'true']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(2, $json['data']);
        $this->assertTrue($json['data'][0]['is_page']);
    }

    public function test_works_with_language(): void
    {
        $this->dataApi($this->blog, '/posts', ['language' => 'fr']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('data', $json);
        $this->assertSame('fr', $json['data'][0]['language']['code']);
    }

    public function test_does_not_work_with_wrong_language(): void
    {
        $this->dataApi($this->blog, '/posts', ['language' => 'jp']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_gives_correct_limit(): void
    {
        $this->dataApi($this->blog, '/posts', ['limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json['data']);
    }

    public function test_gives_correct_page_for_pagination(): void
    {
        $this->dataApi($this->blog, '/posts', ['limit' => 2, 'page' => 2, 'sort' => 'id ASC']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($this->posts[2]->getId(), $json['data'][0]['id']);
    }

    public function test_does_not_work_for_invalid_limit(): void
    {
        $this->dataApi($this->blog, '/posts', ['limit' => 0]);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_does_not_work_for_invalid_page(): void
    {
        $this->dataApi($this->blog, '/posts', ['page' => -1]);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_does_not_work_for_invalid_sort(): void
    {
        $this->dataApi($this->blog, '/posts', ['sort' => 'something_invalid']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_does_not_work_for_invalid_sort_method(): void
    {
        $this->dataApi($this->blog, '/posts', ['sort' => 'published_at SOME']);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_sorts_by_published_at_desc(): void
    {
        $this->dataApi($this->blog, '/posts', ['sort' => 'published_at', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertGreaterThanOrEqual($json['data'][1]['published_at'], $json['data'][0]['published_at']);
        $this->assertGreaterThanOrEqual($json['data'][2]['published_at'], $json['data'][1]['published_at']);
    }

    public function test_sorts_by_published_at_asc(): void
    {
        $this->dataApi($this->blog, '/posts', ['sort' => 'published_at ASC', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertLessThanOrEqual($json['data'][1]['published_at'], $json['data'][0]['published_at']);
        $this->assertLessThanOrEqual($json['data'][2]['published_at'], $json['data'][1]['published_at']);
    }

    public function test_sorts_by_id_desc(): void
    {
        $this->dataApi($this->blog, '/posts', ['sort' => 'id', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertGreaterThanOrEqual($json['data'][1]['id'], $json['data'][0]['id']);
        $this->assertGreaterThanOrEqual($json['data'][2]['id'], $json['data'][1]['id']);
    }

    public function test_sorts_by_is_featured_desc(): void
    {
        $featuredPost = $this->posts[0];
        $em = $this->getEm();
        $post = $em->find(\App\Entity\Post::class, $featuredPost->getId());
        $post->setIsFeatured(true);
        $em->flush();

        $this->dataApi($this->blog, '/posts', ['sort' => 'is_featured DESC', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertTrue($json['data'][0]['is_featured']);
        $this->assertSame($featuredPost->getId(), $json['data'][0]['id']);
    }

    public function test_correctly_filters_by_defined_keys(): void
    {
        $this->dataApi($this->blog, '/posts', ['keys' => 'id', 'limit' => 3]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(3, $json['data']);
        $this->assertArrayHasKey('id', $json['data'][0]);
        $this->assertArrayNotHasKey('slug', $json['data'][0]);
    }

    public function test_filters_posts_by_id(): void
    {
        $post = $this->posts[0];

        $this->dataApi($this->blog, '/posts', ['filter' => 'id=' . $post->getId()]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame($post->getId(), $json['data'][0]['id']);
    }

    public function test_filters_by_is_featured(): void
    {
        $em = $this->getEm();
        $post = $em->find(\App\Entity\Post::class, $this->posts[1]->getId());
        $post->setIsFeatured(true);
        $em->flush();

        $this->dataApi($this->blog, '/posts', ['filter' => 'is_featured=true']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame($post->getId(), $json['data'][0]['id']);
    }

    public function test_filters_by_slug(): void
    {
        $post = $this->posts[0];
        $em = $this->getEm();
        $variant = $em->getRepository(\App\Entity\PostVariant::class)->findOneBy([
            'post' => $post,
            'language' => $this->primaryLanguage,
        ]);
        $variant->setSlug('my-unique-slug-test');
        $em->flush();

        $this->dataApi($this->blog, '/posts', ['filter' => "slug=my-unique-slug-test"]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json['data']);
        $this->assertSame($post->getId(), $json['data'][0]['id']);
    }

    public function test_filters_by_tag_id(): void
    {
        $tag = TagFactory::createOne(['blog' => $this->blog, 'slug' => 'filter-tag', 'is_private' => false]);
        TagFactory::createOne(['blog' => $this->blog, 'slug' => 'filter-tag-v', 'is_private' => false]);
        $post = $this->posts[0];
        PostTagFactory::createOne(['post' => $post, 'post_id' => $post->getId(), 'tag' => $tag, 'tag_id' => $tag->getId()]);

        $this->dataApi($this->blog, '/posts', ['filter' => 'tag.id=' . $tag->getId()]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($post->getId(), $json['data'][0]['id']);
    }

    public function test_filters_by_tag_slug(): void
    {
        $tag = TagFactory::createOne(['blog' => $this->blog, 'slug' => 'my-filter-tag-slug', 'is_private' => false]);
        $post = $this->posts[0];
        PostTagFactory::createOne(['post' => $post, 'post_id' => $post->getId(), 'tag' => $tag, 'tag_id' => $tag->getId()]);

        $this->dataApi($this->blog, '/posts', ['filter' => "tag.slug='my-filter-tag-slug'"]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($post->getId(), $json['data'][0]['id']);
    }

    public function test_filters_by_author_id(): void
    {
        $user = UserFactory::createOne(['blog' => $this->blog, 'posts_count' => 5]);
        $post = $this->posts[0];
        PostAuthorFactory::createOne(['post' => $post, 'post_id' => $post->getId(), 'user' => $user, 'user_id' => $user->getId()]);

        $this->dataApi($this->blog, '/posts', ['filter' => 'author.id=' . $user->getId()]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($post->getId(), $json['data'][0]['id']);
    }

    public function test_filters_by_author_slug(): void
    {
        $user = UserFactory::createOne(['blog' => $this->blog, 'posts_count' => 5, 'slug' => 'my-author-slug-test']);
        $post = $this->posts[0];
        PostAuthorFactory::createOne(['post' => $post, 'post_id' => $post->getId(), 'user' => $user, 'user_id' => $user->getId()]);

        $this->dataApi($this->blog, '/posts', ['filter' => "author.slug='my-author-slug-test'"]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($post->getId(), $json['data'][0]['id']);
    }
}
