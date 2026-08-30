<?php

namespace App\Tests\Service\Ai\Agent\Tool\Query;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Service\Ai\Agent\Tool\Query\QueryTool;
use App\Service\Language\LanguageService;
use App\Service\Post\PostService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;

#[CoversClass(QueryTool::class)]
class QueryToolTest extends KernelTestCase
{

    private function createTool(Blog $blog): QueryTool
    {
        return new QueryTool(
            $blog,
            $this->getService(TagService::class),
            $this->getService(UserService::class),
            $this->getService(PostService::class),
            $this->getService(LanguageService::class),
            // $this->getService(LoggerInterface::class),
        );
    }

    public function test_get_tags_returns_expected_shape(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $tag = TagFactory::createOne(['blog' => $blog, 'is_private' => true, 'posts_count' => 5]);
        TagVariantFactory::createOne([
            'tag' => $tag,
            'language' => $language,
            'name' => 'My Tag',
            'description' => 'A description',
        ]);

        $tool = $this->createTool($blog);
        $result = $tool->getTags();

        $this->assertCount(1, $result);
        $this->assertSame($tag->getId(), $result[0]['id']);
        $this->assertTrue($result[0]['is_private']);
        $this->assertSame($tag->getSlug(), $result[0]['slug']);
        $this->assertSame('My Tag', $result[0]['name']);
        $this->assertSame('A description', $result[0]['description']);
        $this->assertSame(5, $result[0]['posts_count']);
    }

    public function test_get_tags_search_filters_by_name(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $matching = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $matching, 'language' => $language, 'name' => 'Matching name']);

        $other = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $other, 'language' => $language, 'name' => 'Another name']);

        $tool = $this->createTool($blog);
        $result = $tool->getTags(search: 'Match');

        $this->assertCount(1, $result);
        $this->assertSame($matching->getId(), $result[0]['id']);
    }

    public function test_get_tags_respects_limit_and_offset(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOnePrimaryFor($blog);

        for ($i = 0; $i < 3; $i++) {
            TagFactory::createOne(['blog' => $blog]);
        }

        $tool = $this->createTool($blog);

        $this->assertCount(2, $tool->getTags(limit: 2));
        $this->assertCount(1, $tool->getTags(limit: 2, offset: 2));
    }

    public function test_get_authors_returns_expected_shape(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $user = UserFactory::createOne(['blog' => $blog, 'posts_count' => 3]);
        UserVariantFactory::createOne(['user' => $user, 'language' => $language, 'name' => 'Jane Doe']);

        $tool = $this->createTool($blog);
        $result = $tool->getAuthors();

        $this->assertCount(1, $result);
        $this->assertSame($user->getId(), $result[0]['id']);
        $this->assertSame($user->getSlug(), $result[0]['slug']);
        $this->assertSame('Jane Doe', $result[0]['name']);
        $this->assertSame(3, $result[0]['posts_count']);
    }

    public function test_get_authors_search_filters_by_name(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $matching = UserFactory::createOne(['blog' => $blog]);
        UserVariantFactory::createOne(['user' => $matching, 'language' => $language, 'name' => 'Findme']);

        $other = UserFactory::createOne(['blog' => $blog]);
        UserVariantFactory::createOne(['user' => $other, 'language' => $language, 'name' => 'Other']);

        $tool = $this->createTool($blog);
        $result = $tool->getAuthors(search: 'Find');

        $this->assertCount(1, $result);
        $this->assertSame($matching->getId(), $result[0]['id']);
    }

    public function test_get_post_variants_returns_expected_shape(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOnePrimaryFor($blog, ['code' => 'en']);

        $post = PostFactory::createOneFor($blog);
        $variant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'hello-world',
            'title' => 'Hello World',
            'description' => 'A description',
        ]);

        $tool = $this->createTool($blog);
        $result = $tool->getPostVariants('en');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame($variant->getId(), $result[0]['id']);
        $this->assertSame('hello-world', $result[0]['slug']);
        $this->assertSame('published', $result[0]['status']);
        $this->assertSame('Hello World', $result[0]['title']);
        $this->assertSame('A description', $result[0]['description']);
    }

    public function test_get_post_variants_returns_message_for_unknown_language(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOnePrimaryFor($blog, ['code' => 'en']);

        $tool = $this->createTool($blog);
        $result = $tool->getPostVariants('fr');

        $this->assertSame("Language with code 'fr' not found.", $result);
    }

    public function test_get_post_variants_filters_by_id_and_slug(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOnePrimaryFor($blog, ['code' => 'en']);

        $post1 = PostFactory::createOneFor($blog);
        $variant1 = PostVariantFactory::createOne([
            'post' => $post1,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'post-one',
        ]);

        $post2 = PostFactory::createOneFor($blog);
        PostVariantFactory::createOne([
            'post' => $post2,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'post-two',
        ]);

        $tool = $this->createTool($blog);

        $byId = $tool->getPostVariants('en', id: $post1->getId());
        $this->assertIsArray($byId);
        $this->assertCount(1, $byId);
        $this->assertSame($variant1->getId(), $byId[0]['id']);

        $bySlug = $tool->getPostVariants('en', slug: 'post-two');
        $this->assertIsArray($bySlug);
        $this->assertCount(1, $bySlug);
        $this->assertSame('post-two', $bySlug[0]['slug']);
    }

    public function test_get_post_variants_filters_by_status(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOnePrimaryFor($blog, ['code' => 'en']);

        $publishedPost = PostFactory::createOneFor($blog);
        PostVariantFactory::createOne([
            'post' => $publishedPost,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'published-post',
        ]);

        $draftPost = PostFactory::createOneFor($blog);
        PostVariantFactory::createOne([
            'post' => $draftPost,
            'language' => $language,
            'status' => PostVariantStatus::DRAFT,
            'slug' => 'draft-post',
        ]);

        $tool = $this->createTool($blog);
        $result = $tool->getPostVariants('en', status: 'draft');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame('draft-post', $result[0]['slug']);
    }

}
