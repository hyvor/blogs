<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\Language;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\PreviewProcessor;
use App\Service\Post\PostService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(PreviewProcessor::class)]
class PreviewTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    private function postService(): PostService
    {
        return $this->getService(PostService::class);
    }

    /** @return array{Blog, Language, Language} */
    private function createBlogWithLanguagesAndRoutes(): array
    {
        $blog = BlogFactory::createOne();
        $primary = LanguageFactory::createOnePrimaryFor($blog, ['code' => 'en']);
        $secondary = LanguageFactory::createOneFor($blog, ['is_primary' => false, 'code' => 'fr']);
        RouteFactory::createOneFor($blog, ['name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'posts_filter' => null, 'is_enabled' => true]);
        RouteFactory::createOneFor($blog, ['name' => 'page', 'match' => '/{slug}', 'template' => 'page,post', 'posts_filter' => null, 'is_enabled' => true]);
        return [$blog, $primary, $secondary];
    }

    public function test_matches_preview_page(): void
    {
        [$blog, $language] = $this->createBlogWithLanguagesAndRoutes();

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOneFor($post, [
            'slug' => 'my-post-slug',
            'status' => PostVariantStatus::PUBLISHED,
        ], $language);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'post.twig',
            'content' => '{{ _post.id }}{{ _lang.code }}',
        ]);

        $id = $this->postService()->getPreviewId($post);
        $response = $this->pathMatcher()->match($blog, "/p/$id/{$language->getCode()}");

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(false, $response->cache);
        $this->assertSame(200, $response->status);
        $this->assertSame($post->getId() . $language->getCode(), $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_matches_preview_for_a_page(): void
    {
        [$blog, $language] = $this->createBlogWithLanguagesAndRoutes();

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => true]);
        PostVariantFactory::createOneFor($post, [
            'slug' => 'my-page-slug',
            'status' => PostVariantStatus::PUBLISHED,
        ], $language);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'page.twig',
            'content' => '{{ _post.id }}',
        ]);

        $id = $this->postService()->getPreviewId($post);
        $response = $this->pathMatcher()->match($blog, "/p/$id/{$language->getCode()}");

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame((string)$post->getId(), $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_shows_unsaved_content_if_it_exists(): void
    {
        [$blog, $language] = $this->createBlogWithLanguagesAndRoutes();

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOneFor($post, [
            'slug' => 'my-post-slug',
            'status' => PostVariantStatus::PUBLISHED,
            'content_unsaved' => json_encode([
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Unsaved content',
                            ],
                        ],
                    ],
                ],
            ]),
        ], $language);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'post.twig',
            'content' => '{{ _post.content | raw }}',
        ]);

        $id = $this->postService()->getPreviewId($post);
        $response = $this->pathMatcher()->match($blog, "/p/$id/{$language->getCode()}");

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertStringContainsString('Unsaved content', (string)$response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_language_works(): void
    {
        [$blog, , $secondary] = $this->createBlogWithLanguagesAndRoutes();

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOneFor($post, [
            'slug' => 'fr-slug',
            'status' => PostVariantStatus::PUBLISHED,
        ], $secondary);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'post.twig',
            'content' => '{{ _post.id }}{{ _lang.code }}',
        ]);

        $id = $this->postService()->getPreviewId($post);
        $response = $this->pathMatcher()->match($blog, "/p/$id/fr");

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame($post->getId() . 'fr', $response->content);
    }

    public function test_does_not_match_if_preview_id_is_invalid(): void
    {
        [$blog, $language] = $this->createBlogWithLanguagesAndRoutes();

        $response = $this->pathMatcher()->match($blog, "/p/!!!/{$language->getCode()}");

        $this->assertSame(404, $response->status);
    }

    public function test_does_not_match_if_language_does_not_exist(): void
    {
        [$blog] = $this->createBlogWithLanguagesAndRoutes();

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);

        $id = $this->postService()->getPreviewId($post);
        $response = $this->pathMatcher()->match($blog, "/p/$id/de");

        $this->assertSame(404, $response->status);
    }

    public function test_does_not_match_if_post_does_not_exist(): void
    {
        [$blog, $language] = $this->createBlogWithLanguagesAndRoutes();

        $response = $this->pathMatcher()->match($blog, "/p/zzzzzz/{$language->getCode()}");

        $this->assertSame(404, $response->status);
    }

    public function test_does_not_match_post_from_another_blog(): void
    {
        [$blog, $language] = $this->createBlogWithLanguagesAndRoutes();
        $otherBlog = BlogFactory::createOne();

        $post = PostFactory::createOne(['blog' => $otherBlog, 'is_page' => false]);

        $id = $this->postService()->getPreviewId($post);
        $response = $this->pathMatcher()->match($blog, "/p/$id/{$language->getCode()}");

        $this->assertSame(404, $response->status);
    }
}
