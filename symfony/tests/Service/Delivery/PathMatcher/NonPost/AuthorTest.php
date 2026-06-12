<?php

namespace App\Tests\Service\Delivery\PathMatcher\NonPost;

use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\TemplateRenderer\TemplateRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(TemplateRendererService::class)]
class AuthorTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    private function createBlogWithLanguageAndRoutes(): \App\Entity\Blog
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'post', 'match' => '/{slug}', 'template' => 'post', 'posts_filter' => null, 'is_enabled' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'page', 'match' => '/{slug}', 'template' => 'page,post', 'posts_filter' => null, 'is_enabled' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'index', 'match' => '/', 'template' => 'index', 'posts_filter' => '', 'is_enabled' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'tag', 'match' => '/tag/{slug}', 'template' => 'tag,index', 'posts_filter' => 'tag.slug={slug}', 'is_enabled' => true]);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'author', 'match' => '/author/{slug}', 'template' => 'author,index', 'posts_filter' => 'author.slug={slug}', 'is_enabled' => true]);
        return $blog;
    }

    public function test_matches_author_page(): void
    {
        $content = 'I am an author';
        $blog = $this->createBlogWithLanguageAndRoutes();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'author.twig',
            'content' => $content,
        ]);
        UserFactory::createOne(['blog' => $blog, 'slug' => 'my-author']);

        $response = $this->pathMatcher()->match($blog, '/author/my-author');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame($content, $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }
}
