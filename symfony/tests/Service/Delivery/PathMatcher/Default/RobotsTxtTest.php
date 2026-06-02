<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default;

use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\RobotsTxtProcessor;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(RobotsTxtProcessor::class)]
class RobotsTxtTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_returns_robots_txt(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => \App\Entity\Enum\BlogHostingAt::SUBDOMAIN]);
        LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'is_primary' => true,
        ]);

        $response = $this->pathMatcher()->match($blog, '/robots.txt');

        $blogUrl = $this->getService(\App\Service\Route\PermalinkService::class)->getBlogUrl($blog);

        $this->assertSame('text/plain', $response->mimeType);
        $this->assertSame(200, $response->status);
        $this->assertSame(
            "User-agent: *\nSitemap: $blogUrl/sitemap.xml\nDisallow: /p/",
            $response->content
        );
    }
}
