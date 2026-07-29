<?php

namespace App\Tests\Service\Delivery\PathMatcher\Post;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\Meta\BlogMeta;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\TemplateRenderer\TemplateRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(TemplateRendererService::class)]
class PostTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    /** @return array{\App\Entity\Blog, \App\Entity\Language} */
    private function createBlogWithLanguageAndRoutes(): array
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        return [$blog, $blog->getLanguages()[0]];
    }

    public function test_matches_a_post(): void
    {
        $content = <<<TXT
        Meta Title: {{ _meta.title }}
        Meta Description: {{ _meta.description }}
        Meta Featured Image: {{ _meta.featured_image }}
        Meta URL: {{ _meta.url }}
        Post Slug: {{ _post.slug }}
        Comments: {{ _comments }}
        Newsletters: {{ _newsletter }}
        TXT;

        $expected = <<<TXT
        Meta Title: My Post Title
        Meta Description: My Post Description
        Meta Featured Image: https://example.com/image.jpg
        Meta URL: https://example.hyvorblogs.io/my-post-slug
        Post Slug: my-post-slug
        Comments: ht
        Newsletters: hp
        TXT;

        $blogMeta = new BlogMeta();
        $blogMeta->comments_code = 'ht';
        $blogMeta->newsletter_code = 'hp';
        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'meta' => $blogMeta,
            'subdomain' => 'example',
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
        ]);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false,
            'featured_image_url' => 'https://example.com/image.jpg',]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $blog->getLanguages()[0],
            'slug' => 'my-post-slug',
            'status' => PostVariantStatus::PUBLISHED,
            'title' => 'My Post Title',
            'description' => 'My Post Description',
        ]);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'post.twig',
            'content' => $content,
        ]);

        $response = $this->pathMatcher()->match($blog, '/my-post-slug');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame($expected, $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_matches_a_page(): void
    {
        $twig = '{{ _post.id }}';
        [$blog, $language] = $this->createBlogWithLanguageAndRoutes();

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => true]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'slug' => 'my-page-slug',
            'status' => PostVariantStatus::PUBLISHED,
        ]);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'page.twig',
            'content' => $twig,
        ]);

        $response = $this->pathMatcher()->match($blog, '/my-page-slug');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame((string)$post->getId(), $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_matches_post_with_language(): void
    {
        $twig = '{{ _post.id }}{{ _lang.id }}';
        $blog = BlogFactory::createOne();
        $primaryLang = LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        $secondaryLang = LanguageFactory::createOne(['blog' => $blog, 'is_primary' => false, 'code' => 'fr']);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'posts_filter' => null, 'is_enabled' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'page', 'match' => '/{slug}', 'template' => 'page,post', 'posts_filter' => null, 'is_enabled' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'index', 'match' => '/', 'template' => 'index', 'posts_filter' => '', 'is_enabled' => true]);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $primaryLang,
            'slug' => 'en-post-slug',
            'status' => PostVariantStatus::PUBLISHED,
        ]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $secondaryLang,
            'slug' => 'fr-post-slug',
            'status' => PostVariantStatus::PUBLISHED,
        ]);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'post.twig',
            'content' => $twig,
        ]);

        $response = $this->pathMatcher()->match($blog, '/fr/fr-post-slug');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame($post->getId() . $secondaryLang->getId(), $response->content);
    }

    public function test_does_not_match_non_published_posts(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'posts_filter' => null, 'is_enabled' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'page', 'match' => '/{slug}', 'template' => 'page,post', 'posts_filter' => null, 'is_enabled' => true]);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'slug' => 'draft-post',
            'status' => PostVariantStatus::DRAFT,
        ]);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'post.twig',
            'content' => '{{ _post.id }}',
        ]);

        $response = $this->pathMatcher()->match($blog, '/draft-post');
        $this->assertSame(404, $response->status);
    }
}
