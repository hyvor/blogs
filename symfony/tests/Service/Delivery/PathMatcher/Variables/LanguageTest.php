<?php

namespace App\Tests\Service\Delivery\PathMatcher\Variables;

use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
class LanguageTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_uses_primary_language_for_root_path(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => false, 'code' => 'fr']);
        RouteFactory::createDefaultsFor($blog);
        ThemeFileFactory::createIndexTwig($blog, '{{ _lang.code }}');

        // Just test it doesn't throw and falls through to 404
        $response = $this->pathMatcher()->match($blog, '/');
        $this->assertSame('en', $response->content);
    }

    public function test_strips_language_prefix_from_path(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => false, 'code' => 'fr']);
        RouteFactory::createDefaultsFor($blog);
        ThemeFileFactory::createIndexTwig($blog, '{{ _lang.code }}');

        $response = $this->pathMatcher()->match($blog, '/fr/');
        $this->assertSame('fr', $response->content);
    }

    public function test_does_not_strip_invalid_language_prefix(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => false, 'code' => 'fr']);
        RouteFactory::createDefaultsFor($blog);
        ThemeFileFactory::createTemplateTwig($blog, '404.twig', '{{ _lang.code }}');

        // /jp/ - not a registered language, should NOT be stripped, return 404
        $response = $this->pathMatcher()->match($blog, '/jp/');
        $this->assertSame('en', $response->content);
    }
}
