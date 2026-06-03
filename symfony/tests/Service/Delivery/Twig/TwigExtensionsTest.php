<?php

namespace App\Tests\Service\Delivery\Twig;

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
class TwigExtensionsTest extends KernelTestCase
{
    private function render(string $template, mixed $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    // -----------------------------------------------------------------------
    // asset_url
    // -----------------------------------------------------------------------

    public function test_asset_url_filter(): void
    {
        $url = 'https://myblog.com';
        $result = $this->render(
            "{{ 'script.js' | asset_url }}",
            ['_blog' => ['base_url' => $url]]
        );
        $this->assertSame("$url/assets/script.js", $result);
    }

    // -----------------------------------------------------------------------
    // asset
    // -----------------------------------------------------------------------

    public function test_asset_filter_returns_file_contents(): void
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

    public function test_asset_filter_returns_empty_when_not_found(): void
    {
        $blog = BlogFactory::createOne();

        $result = $this->render(
            "{{ 'missing.txt' | asset }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain(), 'base_url' => 'http://x.test']]
        );
        $this->assertSame('', $result);
    }

    // -----------------------------------------------------------------------
    // lang
    // -----------------------------------------------------------------------

    public function test_lang_filter(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'en.yaml',
            'content' => 'test: Test',
        ]);

        $result = $this->render(
            "{{ 'test' | lang }}",
            ['_blog' => ['subdomain' => $blog->getSubdomain()], '_lang' => ['code' => 'en']]
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

    public function test_lang_fallbacks_to_default_language(): void
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

    // -----------------------------------------------------------------------
    // lang_by_number
    // -----------------------------------------------------------------------

    public function test_lang_by_number_zero(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'code' => 'en', 'is_primary' => true]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::LANG,
            'name' => 'en.yaml',
            'content' => "zero: Nothing\none: One\nmulti: Multiple",
        ]);

        $context = ['_blog' => ['subdomain' => $blog->getSubdomain()], '_lang' => ['code' => 'en']];
        $this->assertSame('Nothing', $this->render("{{ 0 | lang_by_number(zero='zero', one='one', multi='multi') }}", $context));
        $this->assertSame('One', $this->render("{{ 1 | lang_by_number(zero='zero', one='one', multi='multi') }}", $context));
        $this->assertSame('Multiple', $this->render("{{ 100 | lang_by_number(zero='zero', one='one', multi='multi') }}", $context));
    }

    // -----------------------------------------------------------------------
    // template
    // -----------------------------------------------------------------------

    public function test_template_filter(): void
    {
        $result = $this->render(
            '{{ var1 | template }}',
            ['var1' => '{{ var2 }}', 'var2' => 'test']
        );
        $this->assertSame('test', $result);
    }

    // -----------------------------------------------------------------------
    // pagination_page_url
    // -----------------------------------------------------------------------

    public function test_pagination_page_url(): void
    {
        $baseUrl = 'https://myblog.com';

        $this->assertSame($baseUrl, $this->render(
            '{{ 1 | pagination_page_url }}',
            ['_meta' => ['url' => $baseUrl]]
        ));
        $this->assertSame("$baseUrl/page/2", $this->render(
            '{{ 2 | pagination_page_url }}',
            ['_meta' => ['url' => $baseUrl]]
        ));
        $this->assertSame($baseUrl, $this->render(
            '{{ 1 | pagination_page_url }}',
            ['_meta' => ['url' => "$baseUrl/page/2"]]
        ));
        $this->assertSame("$baseUrl/page/2", $this->render(
            '{{ 2 | pagination_page_url }}',
            ['_meta' => ['url' => "$baseUrl/page/3"]]
        ));
    }

    // -----------------------------------------------------------------------
    // is_current_url
    // -----------------------------------------------------------------------

    public function test_is_current_url_matches(): void
    {
        $baseUrl = 'https://myblog.com';
        $result = $this->render(
            "{% if is_current_url('$baseUrl/page') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => "$baseUrl/page"]]
        );
        $this->assertSame('yes', $result);
    }

    public function test_is_current_url_wrong_path(): void
    {
        $baseUrl = 'https://myblog.com';
        $result = $this->render(
            "{% if is_current_url('$baseUrl/page') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => "$baseUrl/wrong"]]
        );
        $this->assertSame('', $result);
    }

    public function test_is_current_url_wrong_domain(): void
    {
        $baseUrl = 'https://myblog.com';
        $result = $this->render(
            "{% if is_current_url('$baseUrl/page') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => 'https://other.com/page']]
        );
        $this->assertSame('', $result);
    }

    public function test_is_current_url_relative(): void
    {
        $baseUrl = 'https://myblog.com';
        $result = $this->render(
            "{% if is_current_url('page') %}yes{% endif %}",
            ['_blog' => ['base_url' => $baseUrl], '_meta' => ['url' => "$baseUrl/page"]]
        );
        $this->assertSame('yes', $result);
    }
}
