<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
#[CoversClass(TwigRendererService::class)]
class LangByNumberFilterTest extends KernelTestCase
{
    /**
     * @param array<string, mixed> $context
     */
    private function render(string $template, array $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    private function blogWithLangFile(): Blog
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'en.yaml',
            'content' => "zero: Nothing\none: One\nmulti: Multiple",
        ]);

        return $blog;
    }

    public function test_works_for_zero(): void
    {
        $blog = $this->blogWithLangFile();

        $result = $this->render(
            "{{ 0 | lang_by_number(zero='zero', one='one', multi='multi') }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain()], '_lang' => ['code' => 'en']]
        );
        $this->assertSame('Nothing', $result);
    }

    public function test_works_for_one(): void
    {
        $blog = $this->blogWithLangFile();

        $result = $this->render(
            "{{ 1 | lang_by_number(zero='zero', one='one', multi='multi') }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain()], '_lang' => ['code' => 'en']]
        );
        $this->assertSame('One', $result);
    }

    public function test_works_for_multi(): void
    {
        $blog = $this->blogWithLangFile();

        $result = $this->render(
            "{{ 100 | lang_by_number(zero='zero', one='one', multi='multi') }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain()], '_lang' => ['code' => 'en']]
        );
        $this->assertSame('Multiple', $result);
    }
}
