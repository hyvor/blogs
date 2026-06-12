<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default\Sitemap;

use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\Sitemap\SitemapPagesProcessor;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

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
        $blog = BlogFactory::createOne(['hosting_at' => \App\Entity\Enum\BlogHostingAt::SUBDOMAIN]);
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
        $this->assertStringContainsString('<loc>', (string)$xml);
        $this->assertStringContainsString('hreflang="en"', (string)$xml);
        $this->assertStringContainsString('hreflang="fr"', (string)$xml);
        $this->assertStringContainsString('hreflang="de"', (string)$xml);
    }

    public function test_generates_entries_for_pages(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => \App\Entity\Enum\BlogHostingAt::SUBDOMAIN]);
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'en',
            'is_primary' => true,
        ]);
        RouteFactory::createOne([
            'blog' => $blog,
            'name' => 'page',
            'match' => '/{slug}',
            'template' => 'page.twig',
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
                'language' => $lang,
                'status' => \App\Entity\Enum\PostVariantStatus::PUBLISHED,
                'slug' => 'page-' . $i,
            ]);
        }
        // 1 published post (should NOT appear)
        $post = PostFactory::createOne([
            'blog' => $blog,
            'is_page' => false,
        ]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $lang,
            'status' => \App\Entity\Enum\PostVariantStatus::PUBLISHED,
            'slug' => 'a-post',
        ]);

        $response = $this->pathMatcher()->match($blog, '/sitemap-pages.xml');

        $this->assertSame(200, $response->status);
        $this->assertStringContainsString('/page-0', (string)$response->content);
        $this->assertStringContainsString('/page-1', (string)$response->content);
        $this->assertStringNotContainsString('/a-post', (string)$response->content);
    }
}
