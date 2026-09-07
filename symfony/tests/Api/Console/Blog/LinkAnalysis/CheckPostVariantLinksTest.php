<?php

namespace App\Tests\Api\Console\Blog\LinkAnalysis;

use App\Api\Console\Controller\LinkAnalysisController;
use App\Entity\Enum\UserRole;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\LinkAnalyzerLinkFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(LinkAnalysisController::class)]
class CheckPostVariantLinksTest extends ApiTestCase
{
    /**
     * @return array{0: \App\Entity\Blog, 1: \App\Entity\User, 2: \App\Entity\PostVariant}
     */
    private function setupBlog(string $subdomain): array
    {
        $blog = BlogFactory::createOne(['subdomain' => $subdomain]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        $postVariant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
        ]);

        return [$blog, $user, $postVariant];
    }

    private function mockHttpClient(): void
    {
        $client = new MockHttpClient(function (string $method, string $url) {
            if (str_contains($url, 'hyvor.com')) {
                return new MockResponse('', ['http_code' => 200]);
            }
            if (str_contains($url, 'endpoint.com')) {
                return new MockResponse('', ['http_code' => 404]);
            }
            return new MockResponse('', ['http_code' => 200]);
        });

        static::getContainer()->set(HttpClientInterface::class, $client);
    }

    public function test_returns_404_when_post_variant_not_on_blog(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'link-analysis-check-wrong-blog']);
        $otherBlog = BlogFactory::createOne(['subdomain' => 'link-analysis-check-other-blog']);
        $otherVariant = PostVariantFactory::createOne([
            'post' => PostFactory::createOne(['blog' => $otherBlog]),
            'language' => LanguageFactory::createOnePrimaryFor($otherBlog),
        ]);

        $this->consoleBlogApi('POST', $blog, '/link-analysis/check-urls', [
            'post_variant_id' => $otherVariant->getId(),
            'urls' => ['https://hyvor.com'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_checks_post_variant_links(): void
    {
        $this->mockHttpClient();
        [$blog, $user, $postVariant] = $this->setupBlog('link-analysis-check-urls');

        $this->consoleBlogApi('POST', $blog, '/link-analysis/check-urls', [
            'post_variant_id' => $postVariant->getId(),
            'urls' => ['https://hyvor.com', 'https://endpoint.com'],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(2, $json);
        $this->assertIsArray($json[0]);
        $this->assertIsArray($json[1]);
        $this->assertSame('https://hyvor.com', $json[0]['url']);
        $this->assertSame(200, $json[0]['status_code']);
        $this->assertSame('https://endpoint.com', $json[1]['url']);
        $this->assertSame(404, $json[1]['status_code']);

        $this->getEm()->refresh($postVariant);
        $this->assertSame([
            'https://hyvor.com' => 200,
            'https://endpoint.com' => 404,
        ], $postVariant->getLinkAnalysis());
    }

    public function test_does_not_clear_links_for_other_urls_on_the_same_variant(): void
    {
        $this->mockHttpClient();
        [$blog, $user, $postVariant] = $this->setupBlog('link-analysis-check-urls-preserve');

        // a link that was recorded by a previous full check, and is not part of this ad-hoc check
        $existingLink = LinkAnalyzerLinkFactory::createOne([
            'blog' => $blog,
            'post_variant' => $postVariant,
            'post_variant_id' => $postVariant->getId(),
            'url' => 'https://other-link.example.com',
            'full_url' => 'https://other-link.example.com',
            'status_code' => 200,
        ]);

        $this->consoleBlogApi('POST', $blog, '/link-analysis/check-urls', [
            'post_variant_id' => $postVariant->getId(),
            'urls' => ['https://hyvor.com'],
        ], user: $user);

        $this->assertResponseIsSuccessful();

        // the ad-hoc check must not wipe out links that weren't part of this request
        $this->getEm()->refresh($existingLink);
        $this->assertSame('https://other-link.example.com', $existingLink->getUrl());
    }
}
