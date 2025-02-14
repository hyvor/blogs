<?php

namespace Tests\Unit\Domains\LinkAnalyzer\Check;

use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\Check\FullBlogAnalyzer;
use App\Domains\LinkAnalyzer\PostVariantsCheck\PostVariantsCheck;
use App\Models\LinkAnalyzerLink;
use Database\Factories\BlogFactory;
use Database\Factories\PostFactory;
use Database\Factories\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\Case\DatabaseTestCase;
use Tests\Helper\Generator\PostContentGenerator;

#[CoversClass(FullBlogAnalyzer::class)]
#[CoversClass(PostVariantsCheck::class)]
class FullBlogAnalyzerTest extends DatabaseTestCase
{

    public function testAnalyzesABlog(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();

        $this->app->bind(HttpClientInterface::class, fn() => new MockHttpClient(function ($method, $url, $options) {
            if (str_contains($url, 'hyvor.com')) {
                return new MockResponse(info: ['http_code' => 200]);
            }
            if (str_contains($url, 'example.com')) {
                return new MockResponse(info: ['http_code' => 301]);
            }
            if (str_contains($url, 'hyvorblogs.io')) {
                return new MockResponse(info: ['http_code' => 200]);
            }
            if (str_contains($url, 'broken.com')) {
                return new MockResponse(info: ['http_code' => 404]);
            }
        }));

        $post1 = PostFactory::oneFor($blog, variantAttr: [
            'status' => PostStatusEnum::PUBLISHED,
            'content' => PostContentGenerator::generateWithLinks([
                'https://hyvor.com/about',
                'https://example.com/1',
                'ftp://example.com/2',
                '/about',
                str_pad('https://too-long.com/', 256, 'a'),
            ])
        ]);

        $post2 = PostFactory::oneFor($blog, variantAttr: [
            'status' => PostStatusEnum::PUBLISHED,
            'content' => PostContentGenerator::generateWithLinks([
                'https://hyvor.com/pricing',
                'https://broken.com/1',
            ])
        ]);

        $page1 = PostFactory::oneFor($blog, ['is_page' => true], ['status' => PostStatusEnum::PUBLISHED]);

        // ignored: draft
        PostFactory::oneFor($blog, variantAttr: ['status' => PostStatusEnum::DRAFT]);
        // ignore: no links
        PostFactory::oneFor($blog, variantAttr: ['status' => PostStatusEnum::PUBLISHED]);
        // ignored: empty link
        PostFactory::oneFor($blog, variantAttr: [
            'status' => PostStatusEnum::PUBLISHED,
            'content' => PostContentGenerator::generateWithLinks(['']),
        ]);
        // ignored: only links with fragment
        PostFactory::oneFor($blog, variantAttr: [
            'status' => PostStatusEnum::PUBLISHED,
            'content' => PostContentGenerator::generateWithLinks(['#fragment']),
        ]);

        $analyze = new FullBlogAnalyzer($blog);
        $analyze->analyze();

        $this->assertSame(6, $analyze->postsCount);
//        $this->assertSame(1, $analyze->pagesCount);
//        $this->assertSame(5, $analyze->postVariantsCount);
        $this->assertSame(5, $analyze->linksCount);
        $this->assertSame(3, $analyze->linksOkCount);
        $this->assertSame(1, $analyze->linksBrokenCount);
        $this->assertSame(1, $analyze->linksRedirectCount);

        $links = LinkAnalyzerLink::all();
        $this->assertCount(5, $links);

        $post1Links = $links->filter(fn($link) => $link->post_variant_id === $post1->variants[0]?->id)->sortBy(
            'id'
        )->values();

        $post1Link1 = $post1Links[0];
        $this->assertNotNull($post1Link1);
        $this->assertSame('https://hyvor.com/about', $post1Link1->url);
        $this->assertSame(200, $post1Link1->status_code);

        $post1Link2 = $post1Links[1];
        $this->assertNotNull($post1Link2);
        $this->assertSame('https://example.com/1', $post1Link2->url);
        $this->assertSame(301, $post1Link2->status_code);

        $post1Link3 = $post1Links[2];
        $this->assertNotNull($post1Link3);
        $this->assertSame("/about", $post1Link3->url);
        $this->assertSame(200, $post1Link3->status_code);

        $post2Links = $links
            ->filter(fn($link) => $link->post_variant_id === $post2->variants[0]?->id)
            ->values();

        $post2Link1 = $post2Links[0];
        $this->assertNotNull($post2Link1);
        $this->assertSame('https://hyvor.com/pricing', $post2Link1->url);
        $this->assertSame(200, $post2Link1->status_code);

        $post2Link2 = $post2Links[1];
        $this->assertNotNull($post2Link2);
        $this->assertSame('https://broken.com/1', $post2Link2->url);
        $this->assertSame(404, $post2Link2->status_code);

        // updates variant cachee
        $linkAnalysisCache = $post1->variants[0]?->link_analysis;
        $this->assertIsArray($linkAnalysisCache);
        $this->assertSame(200, $linkAnalysisCache['https://hyvor.com/about']);
        $this->assertSame(301, $linkAnalysisCache['https://example.com/1']);
        $this->assertSame(200, $linkAnalysisCache["/about"]);
    }

    public function testItClearsOldLinksButKeepsIgnoredLinksAsIgnored(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();

        $this->app->bind(HttpClientInterface::class, fn() => new MockHttpClient(function ($method, $url, $options) {
            if (str_contains($url, 'hyvor.com')) {
                return new MockResponse(info: ['http_code' => 200]);
            }
            if (str_contains($url, 'example.com')) {
                return new MockResponse(info: ['http_code' => 301]);
            }
        }));

        $post = PostFactory::oneFor($blog, variantAttr: [
            'status' => PostStatusEnum::PUBLISHED,
            'content' => PostContentGenerator::generateWithLinks([
                'https://hyvor.com/about',
                'https://example.com/1',
            ])
        ]);

        $links = LinkAnalyzerLink::factory()->count(5)->create([
            'blog_id' => $blog->id,
            'post_variant_id' => $post->variants[0]?->id,
        ]);
        $links[0]?->update([
            'url' => 'https://hyvor.com/about',
            'ignore' => true
        ]);

        $analyze = new FullBlogAnalyzer($blog);
        $analyze->analyze();

        $this->assertSame(1, $analyze->linksIgnoredCount);

        $this->assertCount(2, LinkAnalyzerLink::all());

        $links = LinkAnalyzerLink::all();

        $this->assertNotNull($links[0]);
        $this->assertSame('https://hyvor.com/about', $links[0]->url);
        $this->assertTrue($links[0]->ignore);
        $this->assertSame(200, $links[0]->status_code);

        $this->assertNotNull($links[1]);
        $this->assertSame('https://example.com/1', $links[1]->url);
        $this->assertFalse($links[1]->ignore);
        $this->assertSame(301, $links[1]->status_code);

        $variant = $post->variants[0]?->refresh();
        $this->assertNotNull($variant);
        $linkAnalysisCache = $variant->link_analysis;
        $this->assertIsArray($linkAnalysisCache);
        $this->assertSame(-2, $linkAnalysisCache['https://hyvor.com/about']);
        $this->assertSame(301, $linkAnalysisCache['https://example.com/1']);
    }

    public function testAnalyzesSubdomainBlogWithInternalLinks(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes([
            'hosting_at' => BlogHostingAtEnum::SELF,
            'hosting_url' => 'https://hypotheticalwebsite.com/blog'
        ]);
        ThemeFileFactory::templateFor($blog, 'hello', 'post.twig');

        $post1 = PostFactory::oneFor($blog, variantAttr: [
            'status' => PostStatusEnum::PUBLISHED,
            'slug' => 'post-1',
            'content' => PostContentGenerator::generateWithLinks([
                'https://hypotheticalwebsite.com/blog/wrong',
            ])
        ]);

        $post2 = PostFactory::oneFor($blog, variantAttr: [
            'status' => PostStatusEnum::PUBLISHED,
            'slug' => 'post-2',
            'content' => PostContentGenerator::generateWithLinks([
                'https://hypotheticalwebsite.com/blog/post-1',
            ])
        ]);

        $analyze = new FullBlogAnalyzer($blog);
        $analyze->analyze();

        $links = LinkAnalyzerLink::all();
        $this->assertCount(2, $links);

        $link1 = $links->first();
        $this->assertNotNull($link1);
        $this->assertSame('https://hypotheticalwebsite.com/blog/wrong', $link1->url);
        $this->assertSame(404, $link1->status_code);

        $link2 = $links->last();
        $this->assertNotNull($link2);
        $this->assertSame('https://hypotheticalwebsite.com/blog/post-1', $link2->url);
        $this->assertSame(200, $link2->status_code);
    }

}