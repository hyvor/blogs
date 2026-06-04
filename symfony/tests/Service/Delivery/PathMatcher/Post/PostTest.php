<?php

namespace App\Tests\Service\Delivery\PathMatcher\Post;

use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\ThemeFileFolder;
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
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'posts_filter' => null, 'is_enabled' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'page', 'match' => '/{slug}', 'template' => 'page,post', 'posts_filter' => null, 'is_enabled' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'index', 'match' => '/', 'template' => 'index', 'posts_filter' => '', 'is_enabled' => true]);
        return [$blog, $language];
    }

    public function test_matches_a_post(): void
    {
        $twig = '{{ _post.id }}';
        [$blog, $language] = $this->createBlogWithLanguageAndRoutes();

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOne([
            'post' => $post,
            'post_id' => $post->getId(),
            'language' => $language,
            'language_id' => $language->getId(),
            'slug' => 'my-post-slug',
            'status' => PostVariantStatus::PUBLISHED,
        ]);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'post.twig',
            'content' => $twig,
        ]);

        $response = $this->pathMatcher()->match($blog, '/my-post-slug');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame((string)$post->getId(), $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_matches_a_page(): void
    {
        $twig = '{{ _post.id }}';
        [$blog, $language] = $this->createBlogWithLanguageAndRoutes();

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => true]);
        PostVariantFactory::createOne([
            'post' => $post,
            'post_id' => $post->getId(),
            'language' => $language,
            'language_id' => $language->getId(),
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
            'post_id' => $post->getId(),
            'language' => $primaryLang,
            'language_id' => $primaryLang->getId(),
            'slug' => 'en-post-slug',
            'status' => PostVariantStatus::PUBLISHED,
        ]);
        PostVariantFactory::createOne([
            'post' => $post,
            'post_id' => $post->getId(),
            'language' => $secondaryLang,
            'language_id' => $secondaryLang->getId(),
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
            'post_id' => $post->getId(),
            'language' => $language,
            'language_id' => $language->getId(),
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
