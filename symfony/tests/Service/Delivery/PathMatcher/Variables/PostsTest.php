<?php

namespace App\Tests\Service\Delivery\PathMatcher\Variables;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
class PostsTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    private function addPublishedPosts(Blog $blog, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            PostFactory::createPublishedOneForWithVariants(
                $blog,
                publishedAt: new \DateTimeImmutable("-$i days"),
            );
        }
    }

    public function test_does_not_set_posts_and_pagination_on_post_page(): void
    {
        $content = <<<TWIG
        {% if _posts is not defined %}posts variable not defined{% endif %}
        {% if _pagination is not defined %}pagination variable not defined{% endif %}
        TWIG;

        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createTemplateTwig($blog, 'post.twig', $content);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $blog->getLanguages()[0],
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'my-post',
        ]);

        $response = $this->pathMatcher()->match($blog, '/my-post');

        $this->assertStringContainsString('posts variable not defined', (string)$response->content);
        $this->assertStringContainsString('pagination variable not defined', (string)$response->content);
    }

    public function test_sets_posts_and_pagination_on_index(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $this->addPublishedPosts($blog, 15);
        ThemeFileFactory::createIndexTwig($blog, '{{ _posts|length }}|{{ _pagination.page }}|{{ _pagination.total }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame('10|1|15', $response->content);
    }

    public function test_works_with_page_number(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $this->addPublishedPosts($blog, 15);
        ThemeFileFactory::createIndexTwig($blog, '{{ _posts|length }}|{{ _pagination.page }}|{{ _pagination.total }}');

        $response = $this->pathMatcher()->match($blog, '/page/2');

        $this->assertSame('5|2|15', $response->content);
    }

    public function test_changes_limit_based_on_posts_per_pagination_config(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $this->addPublishedPosts($blog, 15);
        ThemeFileFactory::createIndexTwig($blog, '{{ _posts|length }}|{{ _pagination.page }}|{{ _pagination.total }}');
        ThemeFileFactory::createOneFor($blog, 'config.yaml', 'POSTS_PER_PAGINATION: 5', null);

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame('5|1|15', $response->content);
    }

    public function test_returns_404_when_posts_are_not_found_for_the_page_number(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $this->addPublishedPosts($blog, 15);
        ThemeFileFactory::createIndexTwig($blog, '{{ _posts|length }}');

        $response = $this->pathMatcher()->match($blog, '/page/3');

        $this->assertSame(404, $response->status);
    }

    public function test_does_not_return_404_for_first_page_when_no_posts_exist(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createIndexTwig($blog, '{{ _posts|length }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame(200, $response->status);
        $this->assertSame('0', $response->content);
    }

    public function test_sets_featured_post_first_in_posts(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        // older, non-featured posts - would normally sort after the featured one
        PostFactory::createPublishedOneForWithVariants($blog, publishedAt: new \DateTimeImmutable('-2 days'));
        PostFactory::createPublishedOneForWithVariants($blog, publishedAt: new \DateTimeImmutable('-1 days'));
        $featuredPost = PostFactory::createPublishedOneForWithVariants(
            $blog,
            ['is_featured' => true],
            publishedAt: new \DateTimeImmutable('-3 days'),
        );

        ThemeFileFactory::createIndexTwig($blog, '{{ _posts[0].id }}|{% if _posts[0].is_featured %}featured{% endif %}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame($featuredPost->getId() . '|featured', $response->content);
    }

    public function test_sets_featured_posts_variable(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        PostFactory::createPublishedOneForWithVariants($blog, publishedAt: new \DateTimeImmutable('-1 days'));
        $featuredPost = PostFactory::createPublishedOneForWithVariants(
            $blog,
            ['is_featured' => true],
            ['title' => 'The Featured Post'],
            new \DateTimeImmutable('-2 days'),
        );

        ThemeFileFactory::createIndexTwig($blog, '{{ _featured_posts[0].title }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame('The Featured Post', $response->content);
    }
}
