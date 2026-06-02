<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default\Sitemap;

use App\Service\Delivery\Processor\Sitemap\UrlPostEntry;
use App\Service\Route\PermalinkService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UrlPostEntry::class)]
class UrlPostEntryTest extends KernelTestCase
{
    private function permalinkService(): PermalinkService
    {
        return $this->getService(PermalinkService::class);
    }

    public function test_generates_url_entry_with_language_variants_and_images(): void
    {
        $blog = BlogFactory::createOne(['hosting_at' => \App\Entity\Enum\BlogHostingAt::SUBDOMAIN]);
        $lang1 = LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'is_primary' => true,
        ]);
        $lang2 = LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'fr',
            'is_primary' => false,
        ]);
        RouteFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'name' => 'post',
            'match' => '/{slug}',
            'template' => 'post.twig',
            'is_enabled' => true,
        ]);

        $post = PostFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'is_page' => false,
        ]);
        $baseUrl = $this->permalinkService()->getBlogUrl($blog);

        $variant1 = PostVariantFactory::createOne([
            'post' => $post,
            'post_id' => $post->getId(),
            'language' => $lang1,
            'language_id' => $lang1->getId(),
            'status' => \App\Entity\Enum\PostVariantStatus::PUBLISHED,
            'slug' => 'my-post',
            'content' => json_encode([
                'type' => 'doc',
                'content' => [
                    ['type' => 'image', 'attrs' => ['src' => $baseUrl . '/image1.png']],
                    ['type' => 'figure', 'content' => [
                        ['type' => 'image', 'attrs' => ['src' => $baseUrl . '/image2.png']],
                        // external — should be excluded
                        ['type' => 'image', 'attrs' => ['src' => 'https://example.com/external.png']],
                    ]],
                ],
            ]),
        ]);
        $variant2 = PostVariantFactory::createOne([
            'post' => $post,
            'post_id' => $post->getId(),
            'language' => $lang2,
            'language_id' => $lang2->getId(),
            'status' => \App\Entity\Enum\PostVariantStatus::PUBLISHED,
            'slug' => 'mon-article',
        ]);

        // Reload post with variants
        $this->getEm()->clear();
        $post = $this->getEm()->find(\App\Entity\Post::class, $post->getId());

        $entry = new UrlPostEntry($post, $this->permalinkService());
        $xml = $entry->toXML();

        $this->assertStringContainsString("<loc>$baseUrl/my-post</loc>", $xml);
        $this->assertStringContainsString('hreflang="en"', $xml);
        $this->assertStringContainsString('hreflang="fr"', $xml);
        $this->assertStringContainsString("$baseUrl/fr/mon-article", $xml);
        $this->assertStringContainsString("<image:loc>$baseUrl/image1.png</image:loc>", $xml);
        $this->assertStringContainsString("<image:loc>$baseUrl/image2.png</image:loc>", $xml);
        $this->assertStringNotContainsString('example.com', $xml);
    }
}
