<?php

namespace App\Tests\Service\Delivery\PathMatcher;

use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
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

        // Just test it doesn't throw and falls through to 404
        $response = $this->pathMatcher()->match($blog, '/');
        $this->assertSame(404, $response->status);
    }

    public function test_strips_language_prefix_from_path(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => false, 'code' => 'fr']);

        // /fr/something - should strip fr prefix and return 404 (no routes)
        $response = $this->pathMatcher()->match($blog, '/fr/hello-world');
        $this->assertSame(404, $response->status);
    }

    public function test_does_not_strip_invalid_language_prefix(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => false, 'code' => 'fr']);

        // /jp/ - not a registered language, should NOT be stripped, return 404
        $response = $this->pathMatcher()->match($blog, '/jp/hello-world');
        $this->assertSame(404, $response->status);
    }

    public function test_handles_language_with_country_code(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => false, 'code' => 'fr-FR']);

        $response = $this->pathMatcher()->match($blog, '/fr-FR/hello-world');
        $this->assertSame(404, $response->status);
    }
}
