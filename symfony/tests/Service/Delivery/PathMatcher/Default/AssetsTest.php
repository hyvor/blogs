<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default;

use App\Service\Delivery\CacheControl;
use App\Service\Delivery\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\AssetsProcessor;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(AssetsProcessor::class)]
class AssetsTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_matches_assets(): void
    {
        $blog = BlogFactory::createOne();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'folder' => 'assets',
            'name' => 'script.js',
            'content' => 'var x = null',
        ]);

        $response = $this->pathMatcher()->match($blog, '/assets/script.js');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame('var x = null', $response->content);
        $this->assertSame('text/javascript', $response->mimeType);
        $this->assertSame(CacheControl::ONE_WEEK, $response->cacheControl);
    }

    public function test_matches_default_assets(): void
    {
        $blog = BlogFactory::createOne();

        $response = $this->pathMatcher()->match($blog, '/assets/flashload.js');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertIsString($response->content);
        $this->assertNotEmpty($response->content);
    }

    public function test_does_not_match_if_asset_not_found(): void
    {
        $blog = BlogFactory::createOne();

        $response = $this->pathMatcher()->match($blog, '/assets/missing.js');

        $this->assertSame(404, $response->status);
    }
}
