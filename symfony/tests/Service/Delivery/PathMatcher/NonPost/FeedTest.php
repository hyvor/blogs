<?php

namespace App\Tests\Service\Delivery\PathMatcher\NonPost;

use App\Service\Delivery\FeedService;
use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(FeedService::class)]
class FeedTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_matches_index_with_feed(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne(['blog' => $blog, 'is_primary' => true, 'code' => 'en']);
        RouteFactory::createOne(['blog' => $blog, 'name' => 'index', 'match' => '/', 'template' => 'index', 'posts_filter' => '', 'is_enabled' => true]);

        $response = $this->pathMatcher()->match($blog, '/feed');

        $this->assertSame(200, $response->status);
        $this->assertSame('application/atom+xml', $response->mimeType);
    }
}
