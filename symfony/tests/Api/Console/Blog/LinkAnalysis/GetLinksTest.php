<?php

namespace App\Tests\Api\Console\Blog\LinkAnalysis;

use App\Api\Console\Controller\LinkAnalysisController;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\UserRole;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\LinkAnalyzerLinkFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkAnalysisController::class)]
class GetLinksTest extends ApiTestCase
{
    /**
     * @return array{0: \App\Entity\Blog, 1: \App\Entity\User, 2: \App\Entity\PostVariant}
     */
    private function setupBlog(string $subdomain): array
    {
        $blog = BlogFactory::createOne(['subdomain' => $subdomain, 'hosting_at' => BlogHostingAt::SUBDOMAIN]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $postVariant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'slug' => 'test-post',
        ]);

        return [$blog, $user, $postVariant];
    }

    public function test_returns_links(): void
    {
        [$blog, $user, $postVariant] = $this->setupBlog('link-analysis-get-links');

        LinkAnalyzerLinkFactory::createOne([
            'blog' => $blog,
            'post_variant' => $postVariant,
            'post_variant_id' => $postVariant->getId(),
            'url' => 'https://example.com',
            'full_url' => 'https://example.com',
            'status_code' => 200,
            'ignore' => false,
        ]);

        $this->consoleBlogApi('GET', $blog, '/link-analysis/links', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertCount(1, $json);
        $this->assertSame('https://example.com', $json[0]['url']);
        $this->assertSame(200, $json[0]['status_code']);
        $this->assertSame('ok', $json[0]['status_type']);
        $this->assertFalse($json[0]['ignored']);
        $this->assertSame($postVariant->getId(), $json[0]['post_variant_id']);
    }

    public function test_filters_by_type(): void
    {
        [$blog, $user, $postVariant] = $this->setupBlog('link-analysis-get-links-filter');

        LinkAnalyzerLinkFactory::createOne([
            'blog' => $blog,
            'post_variant' => $postVariant,
            'post_variant_id' => $postVariant->getId(),
            'url' => 'https://ok.example.com',
            'full_url' => 'https://ok.example.com',
            'status_code' => 200,
            'ignore' => false,
        ]);
        LinkAnalyzerLinkFactory::createOne([
            'blog' => $blog,
            'post_variant' => $postVariant,
            'post_variant_id' => $postVariant->getId(),
            'url' => 'https://broken.example.com',
            'full_url' => 'https://broken.example.com',
            'status_code' => 404,
            'ignore' => false,
        ]);

        $this->consoleBlogApi('GET', $blog, '/link-analysis/links?type=broken', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertCount(1, $json);
        $this->assertSame('https://broken.example.com', $json[0]['url']);
        $this->assertSame('broken', $json[0]['status_type']);
    }

    public function test_does_not_return_links_from_other_blog(): void
    {
        [$blog, $user, $postVariant] = $this->setupBlog('link-analysis-get-links-isolation');

        $otherBlog = BlogFactory::createOne(['subdomain' => 'link-analysis-get-links-other']);
        LinkAnalyzerLinkFactory::createOne([
            'blog' => $otherBlog,
            'status_code' => 200,
            'ignore' => false,
        ]);

        $this->consoleBlogApi('GET', $blog, '/link-analysis/links', user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame([], $this->getJson());
    }
}
