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
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(LinkAnalysisController::class)]
class IgnoreLinkTest extends ApiTestCase
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

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_ignore_and_unignore_link(bool $ignoreStatus): void
    {
        $subdomain = 'link-analysis-ignore-' . ($ignoreStatus ? 'true' : 'false');
        [$blog, $user, $postVariant] = $this->setupBlog($subdomain);

        $link = LinkAnalyzerLinkFactory::createOne([
            'blog' => $blog,
            'post_variant' => $postVariant,
            'post_variant_id' => $postVariant->getId(),
            'url' => 'https://example.com/page',
            'full_url' => 'https://example.com/page',
            'status_code' => 200,
            'ignore' => !$ignoreStatus,
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/link-analysis/ignore-link', [
            'post_variant_id' => $postVariant->getId(),
            'url' => 'https://example.com/page',
            'status' => $ignoreStatus,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($ignoreStatus, $json['ignored']);
        $this->assertSame('https://example.com/page', $json['url']);

        $this->getEm()->refresh($link);
        $this->assertSame($ignoreStatus, $link->isIgnore());
    }

    public function test_ignores_internal_link_with_relative_url(): void
    {
        [$blog, $user, $postVariant] = $this->setupBlog('link-analysis-ignore-internal');

        $link = LinkAnalyzerLinkFactory::createOne([
            'blog' => $blog,
            'post_variant' => $postVariant,
            'post_variant_id' => $postVariant->getId(),
            'url' => '/welcome',
            'full_url' => '/welcome',
            'status_code' => 200,
            'ignore' => false,
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/link-analysis/ignore-link', [
            'post_variant_id' => $postVariant->getId(),
            'url' => '/welcome',
            'status' => true,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertTrue($json['ignored']);
        $this->assertSame('/welcome', $json['url']);

        $this->getEm()->refresh($link);
        $this->assertTrue($link->isIgnore());
    }

    public function test_returns_404_when_link_not_found(): void
    {
        [$blog, $user, $postVariant] = $this->setupBlog('link-analysis-ignore-notfound');

        $this->consoleBlogApi('PATCH', $blog, '/link-analysis/ignore-link', [
            'post_variant_id' => $postVariant->getId(),
            'url' => 'https://notfound.example.com',
            'status' => true,
        ], user: $user);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_returns_404_when_post_variant_not_on_blog(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'link-analysis-ignore-wrong-blog']);
        $otherBlog = BlogFactory::createOne(['subdomain' => 'link-analysis-ignore-other-blog']);
        $otherVariant = PostVariantFactory::createOne([
            'post' => PostFactory::createOne(['blog' => $otherBlog]),
            'language' => LanguageFactory::createOnePrimaryFor($otherBlog),
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/link-analysis/ignore-link', [
            'post_variant_id' => $otherVariant->getId(),
            'url' => 'https://example.com',
            'status' => true,
        ], user: $user);

        $this->assertResponseStatusCodeSame(404);
    }
}
