<?php

namespace App\Tests\Service\Delivery\PathMatcher\NonPost;

use App\Entity\Blog;
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

    private function createBlogWithLanguageAndRoutes(): Blog
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        RouteFactory::createDefaultsFor($blog);
        return $blog;
    }

    public function test_matches_author_page(): void
    {
        $content = 'I am an author: {{ _author.slug }}';
        $blog = $this->createBlogWithLanguageAndRoutes();
        ThemeFileFactory::createTemplateTwig($blog, 'author.twig', $content);
        UserFactory::createOne(['blog' => $blog, 'slug' => 'my-author']);

        $response = $this->pathMatcher()->match($blog, '/author/my-author');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame('I am an author: my-author', $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }
}
