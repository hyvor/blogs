<?php

namespace App\Tests\Service\Delivery\PathMatcher\Template;

use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\TemplateRenderer\TemplateRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(TemplateRendererService::class)]
class TemplateRouteTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_matches_template_route(): void
    {
        $content = 'I am a custom template-based route';
        $blog = BlogFactory::createOne();
        LanguageFactory::createOnePrimaryFor($blog, ['code' => 'en']);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'route-test.twig',
            'content' => $content,
        ]);

        $response = $this->pathMatcher()->match($blog, '/test');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame($content, $response->content);
        $this->assertSame('text/html', $response->mimeType);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
    }

    public function test_sets_custom_mime_type(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOnePrimaryFor($blog, ['code' => 'en']);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'route-test.json.twig',
            'content' => '{}',
        ]);

        $response = $this->pathMatcher()->match($blog, '/test.json');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame('{}', $response->content);
        $this->assertSame('application/json', $response->mimeType);
    }
}
