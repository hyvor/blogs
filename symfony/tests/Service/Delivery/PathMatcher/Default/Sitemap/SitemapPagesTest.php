<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default\Sitemap;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\PostVariantStatus;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\Sitemap\SitemapPagesProcessor;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DomCrawler\Crawler;

#[CoversClass(PathMatcher::class)]
#[CoversClass(SitemapPagesProcessor::class)]
class SitemapPagesTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_generates_entries_for_homepage_and_variants(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN, 'subdomain' => 'test']);
        $primaryLang = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'en',
            'is_primary' => true,
        ]);
        LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'fr',
            'is_primary' => false,
        ]);
        LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'de',
            'is_primary' => false,
        ]);

        $response = $this->pathMatcher()->match($blog, '/sitemap-pages.xml');

        $this->assertSame(200, $response->status);
        $xml = $response->content;

        // homepage entry with 3 language alternates
        $this->assertStringContainsString('<loc>https://test.hyvorblogs.io</loc>', (string)$xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="en" href="https://test.hyvorblogs.io" />', (string)$xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="fr" href="https://test.hyvorblogs.io/fr" />', (string)$xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="de" href="https://test.hyvorblogs.io/de" />', (string)$xml);

        // make sure xml is valid
        $crawler = new Crawler($xml);
        $this->assertCount(1, $crawler->filter('default|url'));
    }

    public function test_generates_entries_for_pages(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => BlogHostingAt::SUBDOMAIN, 'subdomain' => 'test']);
        $primaryLanguage = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'en',
            'is_primary' => true,
        ]);
        $secondaryLanguage = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'fr',
            'is_primary' => false,
        ]);

        RouteFactory::createOne([
            'blog' => $blog,
            'name' => 'page',
            'match' => '/{slug}',
            'template' => 'page',
            'is_enabled' => true,
        ]);

        // 2 published pages
        for ($i = 0; $i < 2; $i++) {
            $post = PostFactory::createOne([
                'blog' => $blog,
                'is_page' => true,
            ]);
            PostVariantFactory::createOne([
                'post' => $post,
                'language' => $primaryLanguage,
                'status' => PostVariantStatus::PUBLISHED,
                'slug' => 'page-' . $i,
            ]);
            if ($i === 1) {
                PostVariantFactory::createOne([
                    'post' => $post,
                    'language' => $secondaryLanguage,
                    'status' => PostVariantStatus::PUBLISHED,
                    'slug' => 'page-' . $i . '-fr',
                ]);
            }
        }
        // 1 published post (should NOT appear)
        $post = PostFactory::createOne([
            'blog' => $blog,
            'is_page' => false,
        ]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $primaryLanguage,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'a-post',
        ]);

        $response = $this->pathMatcher()->match($blog, '/sitemap-pages.xml');

        $this->assertSame(200, $response->status);
        $this->assertStringContainsString('/page-0', (string)$response->content);
        $this->assertStringContainsString('/page-1', (string)$response->content);
        $this->assertStringNotContainsString('/a-post', (string)$response->content);

        $crawler = new Crawler($response->content);
        $this->assertCount(3, $crawler->filter('default|url')); // index + 2 pages
        $this->assertCount(1, $crawler->filter('default|url')->eq(1)->filter('xhtml|link')); // 1 language alternates for first page
        $this->assertCount(2, $crawler->filter('default|url')->eq(2)->filter('xhtml|link')); // 2 language alternates for second page

    }
}
