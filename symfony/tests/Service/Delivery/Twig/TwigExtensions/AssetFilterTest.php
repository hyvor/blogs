<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
class AssetFilterTest extends KernelTestCase
{
    private function render(string $template, mixed $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_returns_the_file_contents(): void
    {
        $blog = BlogFactory::createOne();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::ASSETS,
            'name' => 'test.txt',
            'content' => 'test',
        ]);

        $result = $this->render(
            "{{ 'test.txt' | asset }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain(), 'base_url' => 'http://x.test']]
        );
        $this->assertSame('test', $result);
    }

    public function test_returns_empty_string_when_the_file_is_not_found(): void
    {
        $blog = BlogFactory::createOne();

        $result = $this->render(
            "{{ 'test.txt' | asset }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain(), 'base_url' => 'http://x.test']]
        );
        $this->assertSame('', $result);
    }
}
