<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default\Sitemap;

use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\Sitemap\SitemapIndexProcessor;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(SitemapIndexProcessor::class)]
class SitemapIndexTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_returns_sitemap_with_only_pages(): void
    {
        $blog = BlogFactory::createOne();
        LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'en',
            'is_primary' => true,
        ]);

        $response = $this->pathMatcher()->match($blog, '/sitemap.xml');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
        $this->assertSame(200, $response->status);
        $this->assertSame('text/xml', $response->mimeType);

        $xml = simplexml_load_string((string)$response->content);
        $this->assertNotFalse($xml);
        $sitemaps = $xml->sitemap;
        $this->assertCount(1, $sitemaps);
        $this->assertStringEndsWith('/sitemap-pages.xml', (string)$sitemaps[0]->loc);
    }

    public function test_adds_posts_sitemap_when_posts_exist(): void
    {
        $blog = BlogFactory::createOne();
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'en',
            'is_primary' => true,
        ]);

        // Create 5 published posts
        for ($i = 0; $i < 5; $i++) {
            $post = PostFactory::createOne([
                'blog' => $blog,
                'is_page' => false,
            ]);
            PostVariantFactory::createOne([
                'post' => $post,
                'post_id' => $post->getId(),
                'language' => $lang,
                'language_id' => $lang->getId(),
                'status' => \App\Entity\Enum\PostVariantStatus::PUBLISHED,
                'slug' => 'post-' . $i,
            ]);
        }

        $response = $this->pathMatcher()->match($blog, '/sitemap.xml');

        $xml = simplexml_load_string((string)$response->content);
        $this->assertNotFalse($xml);
        $sitemaps = $xml->sitemap;
        $this->assertCount(2, $sitemaps);
        $this->assertStringEndsWith('/sitemap-pages.xml', (string)$sitemaps[0]->loc);
        $this->assertStringEndsWith('/sitemap-posts-1.xml', (string)$sitemaps[1]->loc);
    }
}
