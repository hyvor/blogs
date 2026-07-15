<?php

namespace App\Tests\Service\Delivery\PathMatcher\Variables;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Meta\BlogMeta;
use App\Entity\Post;
use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\ThemeFileFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
class FootTest extends KernelTestCase
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

    public function test_sets_foot_in_index(): void
    {
        $meta = new BlogMeta();
        $meta->code_foot = 'This is code foot {{ _blog.name }}';

        $blog = BlogFactory::createOneWithLanguageAndRoutes(['meta' => $meta], variants: false);
        BlogVariantFactory::createManyForBlogWithAllLanguages($blog, attributes: ['name' => 'My Blog']);

        ThemeFileFactory::createIndexTwig($blog, '{{ _foot | template }}');

        $response = $this->pathMatcher()->match($blog, '/');
        $content = (string)$response->content;

        $this->assertStringContainsString('flashload.js', $content);
        $this->assertStringContainsString('This is code foot My Blog', $content);
    }

    public function test_sets_foot_in_a_post_page(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $post = PostFactory::createPublishedOneForWithVariants($blog, ['code_foot' => 'A post code foot {{ _post.id }}']);

        ThemeFileFactory::createTemplateTwig($blog, 'post.twig', '{{ _foot | template }}');

        $slug = $this->firstVariantSlug($post);
        $response = $this->pathMatcher()->match($blog, '/' . $slug);

        $this->assertStringContainsString('A post code foot ' . $post->getId(), (string)$response->content);
    }

    public function test_sets_flashload_basepath_for_subdomain_hosting(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(['hosting_at' => BlogHostingAt::SUBDOMAIN]);
        ThemeFileFactory::createIndexTwig($blog, '{{ _foot | template }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertStringContainsString('basePath: ""', (string)$response->content);
    }

    public function test_sets_flashload_basepath_for_self_hosting(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'hosting_at' => BlogHostingAt::SELF,
            'hosting_url' => 'https://hyvor.com/blog',
        ]);
        ThemeFileFactory::createIndexTwig($blog, '{{ _foot | template }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertStringContainsString('basePath: "blog"', (string)$response->content);
    }

    public function test_sets_flashload_basepath_with_nested_path_for_self_hosting(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'hosting_at' => BlogHostingAt::SELF,
            'hosting_url' => 'https://hyvor.com/blog/page',
        ]);
        ThemeFileFactory::createIndexTwig($blog, '{{ _foot | template }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertStringContainsString('basePath: "blog/page"', (string)$response->content);
    }

    public function test_disables_flashload(): void
    {
        $meta = new BlogMeta();
        $meta->flashload = false;

        $blog = BlogFactory::createOneWithLanguageAndRoutes(['meta' => $meta]);
        ThemeFileFactory::createIndexTwig($blog, '{{ _foot | template }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertStringNotContainsString('flashload.js', (string)$response->content);
    }

    public function test_adds_tag_code_foot(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        $post = PostFactory::createPublishedOneForWithVariants($blog);

        $tag = TagFactory::createOne(['blog' => $blog, 'code_foot' => 'This is tag code foot for {{ _post.slug }}']);
        $otherTag = TagFactory::createOne(['blog' => $blog, 'code_foot' => 'Not tag']);
        $post->getTags()->add($tag);

        $this->getService(EntityManagerInterface::class)->flush();

        ThemeFileFactory::createTemplateTwig($blog, 'post.twig', '{{ _foot | template }}');

        $slug = $this->firstVariantSlug($post);
        $response = $this->pathMatcher()->match($blog, '/' . $slug);
        $content = (string)$response->content;

        $this->assertStringContainsString('This is tag code foot for ' . $slug, $content);
        $this->assertStringNotContainsString('Not tag', $content);
    }
}
