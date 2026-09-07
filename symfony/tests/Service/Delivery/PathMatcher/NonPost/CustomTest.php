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
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(TemplateRendererService::class)]
class CustomTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_matches_custom_route_and_template(): void
    {
        $content = 'I am a custom route';
        $blog = BlogFactory::createOne();
        LanguageFactory::createOnePrimaryFor($blog, ['code' => 'en']);
        RouteFactory::createOneFor($blog, ['name' => 'test', 'match' => '/test', 'template' => 'test', 'posts_filter' => null, 'is_enabled' => true]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'test.twig',
            'content' => $content,
        ]);

        $response = $this->pathMatcher()->match($blog, '/test');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame($content, $response->content);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }
}
