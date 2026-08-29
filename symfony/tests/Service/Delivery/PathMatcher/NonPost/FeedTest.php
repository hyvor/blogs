<?php

namespace App\Tests\Service\Delivery\PathMatcher\NonPost;

use App\Entity\Enum\BlogHostingAt;
use App\Service\Delivery\FeedService;
use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\PostFactory;
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
        $blog = BlogFactory::createOneWithLanguageAndRoutes(['hosting_at' => BlogHostingAt::SUBDOMAIN, 'subdomain' => 'myblog']);

        $post = PostFactory::createPublishedOneForWithVariants(
            $blog,
            variantAttributes: ['slug' => 'my-first-post'],
        );

        $response = $this->pathMatcher()->match($blog, '/feed');

        $this->assertIsString($response->content);
        $this->assertSame(200, $response->status);
        $this->assertSame('application/atom+xml', $response->mimeType);

        $contains = [
            '<id>https://myblog.hyvorblogs.io/</id>',
            '<link rel="self" href="https://myblog.hyvorblogs.io/feed"/>',
            '<link href="https://myblog.hyvorblogs.io"/>',
            '<generator uri="https://blogs.hyvor.com">Hyvor Blogs</generator>',
            '<id>https://myblog.hyvorblogs.io/my-first-post</id>',
        ];

        foreach ($contains as $string) {
            $this->assertStringContainsString($string, $response->content);
        }
    }
}
