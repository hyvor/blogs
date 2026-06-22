<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigLanguage;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
#[CoversClass(TwigLanguage::class)]
class LangFilterTest extends KernelTestCase
{
    private function render(string $template, mixed $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_lang(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'en.yaml',
            'content' => 'test: Test',
        ]);

        $result = $this->render(
            "{{ 'test' | lang }}",
            [
                '_blog' => ['subdomain' => $blog->getSubdomain()], 
                '_lang' => ['code' => 'en']
            ]
        );
        $this->assertSame('Test', $result);
    }

    public function test_lang_with_single_placeholder(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'en.yaml',
            'content' => 'test: "* authors"',
        ]);

        $result = $this->render(
            "{{ 'test' | lang(2) }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain()], '_lang' => ['code' => 'en']]
        );
        $this->assertSame('2 authors', $result);
    }

    public function test_lang_with_multiple_placeholders(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'en.yaml',
            'content' => 'test: "Written by {name} {date}"',
        ]);

        $result = $this->render(
            "{{ 'test' | lang(name='hyvor', date='today') }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain()], '_lang' => ['code' => 'en']]
        );
        $this->assertSame('Written by hyvor today', $result);
    }

    public function test_fallbacks_to_default_language(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        LanguageFactory::createOne(['blog' => $blog, 'code' => 'fr', 'is_primary' => false]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'en.yaml',
            'content' => "test1: english-test\ntest2: english",
        ]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'fr.yaml',
            'content' => 'test2: french',
        ]);

        $result = $this->render(
            "{{ 'test1' | lang }}{{ 'test2' | lang }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain()], '_lang' => ['code' => 'fr']]
        );
        $this->assertSame('english-testfrench', $result);
    }

    public function test_works_when_two_blogs_are_rendered_one_after_another(): void
    {
        $blog1 = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog1, 'code' => 'en', 'is_primary' => true]);
        ThemeFileFactory::createOne([
            'blog' => $blog1,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'en.yaml',
            'content' => 'test: Test',
        ]);

        $blog2 = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog2, 'code' => 'en', 'is_primary' => true]);
        ThemeFileFactory::createOne([
            'blog' => $blog2,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'en.yaml',
            'content' => 'test: Test2',
        ]);

        $result1 = $this->render(
            "{{ 'test' | lang }}",
            ['_blog' => ['subdomain' => $blog1->getSubdomain()], '_lang' => ['code' => 'en']]
        );
        $result2 = $this->render(
            "{{ 'test' | lang }}",
            ['_blog' => ['subdomain' => $blog2->getSubdomain()], '_lang' => ['code' => 'en']]
        );

        $this->assertSame('Test', $result1);
        $this->assertSame('Test2', $result2);
    }
}
