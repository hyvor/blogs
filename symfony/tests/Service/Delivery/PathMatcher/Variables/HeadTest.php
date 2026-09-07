<?php

namespace App\Tests\Service\Delivery\PathMatcher\Variables;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Meta\BlogMeta;
use App\Entity\Post;
use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\ThemeFileFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
class HeadTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    private function firstVariantSlug(Post $post): string
    {
        $variant = $post->getVariants()->first();
        $this->assertNotFalse($variant);
        return $variant->getSlug() ?? '';
    }

    public function test_sets_head_in_index(): void
    {
        $meta = new BlogMeta();
        $meta->code_head = 'This is code head {{ _blog.name }}';
        $meta->social_twitter = 'https://twitter.com/HyvorBlogs';

        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'subdomain' => 'example',
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
            'meta' => $meta,
        ], variants: false);
        BlogVariantFactory::createManyForBlogWithAllLanguages($blog, attributes: [
            'name' => 'My Blog',
            'description' => 'My Blog Description',
        ]);

        ThemeFileFactory::createIndexTwig($blog, '{{ _head | template }}');

        $response = $this->pathMatcher()->match($blog, '/');
        $content = (string)$response->content;

        $this->assertStringContainsString('<meta name="generator" content="Hyvor Blogs" />', $content);
        $this->assertStringContainsString(
            '<link rel="stylesheet" href="https://example.hyvorblogs.io/styles.css?v=1" />',
            $content
        );
        $this->assertStringContainsString('This is code head My Blog', $content);
        $this->assertStringContainsString('<title>My Blog</title>', $content);
        $this->assertStringContainsString('<meta name="description" content="My Blog Description" />', $content);
        $this->assertStringContainsString('<link rel="canonical" href="https://example.hyvorblogs.io" />', $content);
        $this->assertStringContainsString('<meta name="twitter:site" content="@HyvorBlogs" />', $content);
    }

    public function test_sets_head_in_a_post_page(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();

        $publishedAt = new \DateTimeImmutable('-2 days');
        $updatedAt = new \DateTimeImmutable('-1 days');

        $post = PostFactory::createPublishedOneForWithVariants(
            $blog,
            ['code_head' => 'A post code head {{ _post.id }}'],
            ['content_updated_at' => $updatedAt],
            $publishedAt,
        );

        $user = UserFactory::createOne(['blog' => $blog, 'social_twitter' => 'https://twitter.com/Author']);
        UserVariantFactory::createOne(['user' => $user, 'language' => $blog->getLanguages()[0], 'name' => 'Author Name']);
        $post->getAuthors()->add($user);

        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOneFor($tag, ['language' => $blog->getLanguages()[0], 'name' => 'My Tag']);
        $post->getTags()->add($tag);

        $this->getService(EntityManagerInterface::class)->flush();

        ThemeFileFactory::createTemplateTwig($blog, 'post.twig', '{{ _head | template }}');

        $response = $this->pathMatcher()->match($blog, '/' . $this->firstVariantSlug($post));
        $content = (string)$response->content;

        $this->assertStringContainsString(
            '<meta property="article:published_time" content="' . $publishedAt->format('c') . '" />',
            $content
        );
        $this->assertStringContainsString(
            '<meta property="article:modified_time" content="' . $updatedAt->format('c') . '" />',
            $content
        );
        $this->assertStringContainsString('<meta property="article:author" content="Author Name" />', $content);
        $this->assertStringContainsString('<meta property="article:section" content="My Tag" />', $content);
        $this->assertStringContainsString('<meta name="twitter:creator" content="@Author" />', $content);
        $this->assertStringContainsString('A post code head ' . $post->getId(), $content);
    }

    public function test_sets_alternate_language_links_for_post_variants(): void
    {
        $blog = BlogFactory::createOne([
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
            'subdomain' => 'example',
        ]);
        $primary = LanguageFactory::createOnePrimaryFor($blog);
        $secondary = LanguageFactory::createOneFor($blog, ['code' => 'fr', 'is_primary' => false]);
        RouteFactory::createDefaultsFor($blog);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOneFor($post, [
            'slug' => 'en-slug',
            'status' => PostVariantStatus::PUBLISHED,
            'published_at' => new \DateTimeImmutable(),
        ], $primary);
        PostVariantFactory::createOneFor($post, [
            'slug' => 'fr-slug',
            'status' => PostVariantStatus::PUBLISHED,
            'published_at' => new \DateTimeImmutable(),
        ], $secondary);

        ThemeFileFactory::createTemplateTwig($blog, 'post.twig', '{{ _head | template }}');

        $response = $this->pathMatcher()->match($blog, '/en-slug');
        $content = (string)$response->content;

        $this->assertStringContainsString('<link rel="alternate" href="https://example.hyvorblogs.io/fr/fr-slug" hreflang="fr" />', $content);
    }

    public function test_adds_nofollow(): void
    {
        $meta = new BlogMeta();
        $meta->seo_indexing = false;

        $blog = BlogFactory::createOneWithLanguageAndRoutes(['meta' => $meta]);
        ThemeFileFactory::createIndexTwig($blog, '{{ _head | template }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertStringContainsString('<meta name="robots" content="noindex">', (string)$response->content);
    }

    public function test_adds_favicon_from_icon_url(): void
    {
        $meta = new BlogMeta();
        $meta->icon_url = 'https://example.com/icon.png';

        $blog = BlogFactory::createOneWithLanguageAndRoutes(['meta' => $meta]);
        ThemeFileFactory::createIndexTwig($blog, '{{ _head | template }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertStringContainsString(
            '<link rel="shortcut icon" href="https://example.com/icon.png" />',
            (string)$response->content
        );
    }

    public function test_adds_favicon_from_logo_when_icon_is_not_set(): void
    {
        $meta = new BlogMeta();
        $meta->logo_url = 'https://example.com/logo.png';

        $blog = BlogFactory::createOneWithLanguageAndRoutes(['meta' => $meta]);
        ThemeFileFactory::createIndexTwig($blog, '{{ _head | template }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertStringContainsString(
            '<link rel="shortcut icon" href="https://example.com/logo.png" />',
            (string)$response->content
        );
    }

    public function test_adds_tag_code_head_to_post_page(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $post = PostFactory::createPublishedOneForWithVariants($blog);

        $tag = TagFactory::createOne(['blog' => $blog, 'code_head' => 'This is tag code head for {{ _post.slug }}']);
        $otherTag = TagFactory::createOne(['blog' => $blog, 'code_head' => 'Not tag']);
        $post->getTags()->add($tag);

        $this->getService(EntityManagerInterface::class)->flush();

        ThemeFileFactory::createTemplateTwig($blog, 'post.twig', '{{ _head | template }}');

        $slug = $this->firstVariantSlug($post);
        $response = $this->pathMatcher()->match($blog, '/' . $slug);
        $content = (string)$response->content;

        $this->assertStringContainsString('This is tag code head for ' . $slug, $content);
        $this->assertStringNotContainsString('Not tag', $content);
    }

    public function test_does_not_add_tag_code_head_to_other_pages(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $post = PostFactory::createPublishedOneForWithVariants($blog);

        $tag = TagFactory::createOne(['blog' => $blog, 'code_head' => 'This is tag code head for {{ _post.slug }}']);
        $post->getTags()->add($tag);

        $this->getService(EntityManagerInterface::class)->flush();

        ThemeFileFactory::createIndexTwig($blog, '{{ _head | template }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertStringNotContainsString('This is tag code head for', (string)$response->content);
    }
}
