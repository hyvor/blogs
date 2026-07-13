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
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(TemplateRendererService::class)]
class TagTest extends KernelTestCase
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

    public function test_matches_tag_page(): void
    {
        $content = 'I am a tag: {{ _tag.slug }}';
        $blog = $this->createBlogWithLanguageAndRoutes();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'tag.twig',
            'content' => $content,
        ]);
        TagFactory::createOne(['blog' => $blog, 'slug' => 'my-tag', 'is_private' => false]);

        $response = $this->pathMatcher()->match($blog, '/tag/my-tag');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame('I am a tag: my-tag', $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_does_not_match_private_tag(): void
    {
        $blog = $this->createBlogWithLanguageAndRoutes();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'tag.twig',
            'content' => 'I am a tag',
        ]);
        TagFactory::createOne(['blog' => $blog, 'slug' => 'private-tag', 'is_private' => true]);

        $response = $this->pathMatcher()->match($blog, '/tag/private-tag');

        $this->assertSame(404, $response->status);
    }
}
