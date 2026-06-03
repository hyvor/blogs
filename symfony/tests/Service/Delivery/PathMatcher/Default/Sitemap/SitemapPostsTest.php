<?php

namespace App\Tests\Service\Delivery\PathMatcher\Default\Sitemap;

use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Service\Delivery\Processor\Sitemap\SitemapPostsProcessor;
use App\Service\Limit;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
#[CoversClass(SitemapPostsProcessor::class)]
class SitemapPostsTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    private function createBlogWithPosts(int $count): array
    {
        $blog = BlogFactory::createOne(['hosting_at' => \App\Entity\Enum\BlogHostingAt::SUBDOMAIN]);
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'is_primary' => true,
        ]);
        RouteFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'name' => 'post',
            'match' => '/{slug}',
            'template' => 'post.twig',
            'is_enabled' => true,
        ]);

        for ($i = 0; $i < $count; $i++) {
            $post = PostFactory::createOne([
                'blog' => $blog,
                'blog_id' => $blog->getId(),
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

        return [$blog, $lang];
    }

    public function test_does_not_work_for_page_0(): void
    {
        [$blog] = $this->createBlogWithPosts(0);

        $response = $this->pathMatcher()->match($blog, '/sitemap-posts-0.xml');

        $this->assertSame(404, $response->status);
    }

    public function test_generates_posts_sitemap(): void
    {
        [$blog] = $this->createBlogWithPosts(5);

        $response = $this->pathMatcher()->match($blog, '/sitemap-posts-1.xml');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame(DeliveryFileType::TEMPLATE, $response->fileType);
        $this->assertSame(200, $response->status);
        $this->assertSame('text/xml', $response->mimeType);

        $xml = simplexml_load_string((string)$response->content);
        $this->assertNotFalse($xml);

        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('default', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urls = $xml->xpath('default:url');
        $this->assertCount(5, $urls);
    }

    public function test_paginates(): void
    {
        // Override limit for this test by creating a custom processor
        [$blog] = $this->createBlogWithPosts(3);

        // With default limit of 2500, all 3 go on page 1
        $response1 = $this->pathMatcher()->match($blog, '/sitemap-posts-1.xml');
        $this->assertSame(200, $response1->status);

        $response2 = $this->pathMatcher()->match($blog, '/sitemap-posts-2.xml');
        // With 3 posts and limit 2500, page 2 is empty → 404
        $this->assertSame(404, $response2->status);
    }
}
