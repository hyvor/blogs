<?php

namespace App\Tests\Service\Delivery\PathMatcher\NonPost;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\TemplateRenderer\TemplateRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(TemplateRendererService::class)]
class IndexTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    private function createBlogWithLanguageAndRoutes(): Blog
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        BlogVariantFactory::createOne(['language' => $lang, 'blog' => $blog, 'name' => 'My Blog']);
        RouteFactory::createDefaultsFor($blog);
        return $blog;
    }

    public function test_matches_index_page(): void
    {
        $content = 'Hello World: {{ _blog.name }}';
        $blog = $this->createBlogWithLanguageAndRoutes();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => $content,
        ]);

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame('Hello World: My Blog', $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_matches_index_page_with_number(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createIndexTwig($blog, '{{ _pagination.page }}|{{ _posts[0].slug }}');
        ThemeFileFactory::createOneFor($blog, 'config.yaml', 'POSTS_PER_PAGINATION: 1', null);

        $post1 = PostFactory::createOne(['blog' => $blog, 'is_page' => false, 'published_at' => new \DateTimeImmutable('-1 day')]);
        $post2 = PostFactory::createOne(['blog' => $blog, 'is_page' => false, 'published_at' => new \DateTimeImmutable('-2 days')]);
        foreach ([$post1, $post2] as $post) {
            PostVariantFactory::createOne([
                'post' => $post,
                'language' => $blog->getLanguages()[0],
                'status' => PostVariantStatus::PUBLISHED,
                'slug' => 'post-' . $post->getId(),
            ]);
        }

        $response = $this->pathMatcher()->match($blog, '/page/2');
        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame('2|post-' . $post2->getId(), $response->content);
    }
}
