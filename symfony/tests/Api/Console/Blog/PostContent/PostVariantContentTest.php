<?php

namespace App\Tests\Api\Console\Blog\PostContent;

use App\Api\Console\Controller\PostContentController;
use App\Service\Post\Content\PostContentConverter;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(PostContentController::class)]
#[CoversClass(PostContentConverter::class)]
class PostVariantContentTest extends ApiTestCase
{
    private const string JSON = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hello "},{"type":"text","marks":[{"type":"strong"}],"text":"world"}]}]}';

    /** @return array{0: \App\Entity\Blog, 1: \App\Entity\User, 2: \App\Entity\Post, 3: \App\Entity\PostVariant} */
    private function setUpVariant(): array
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);
        $variant = PostVariantFactory::createOneFor($post, [
            'language' => $language,
            'content_unsaved' => self::JSON,
            'document_version' => 3,
        ]);
        return [$blog, $user, $post, $variant];
    }

    public function test_get_markdown_by_default(): void
    {
        [$blog, $user, $post] = $this->setUpVariant();

        $this->consoleBlogApi('GET', $blog, "/post/{$post->getId()}/variant/content", user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('markdown', $json['format']);
        $content = $json['content'];
        $this->assertIsString($content);
        $this->assertSame('Hello **world**', trim($content));
        $this->assertSame(3, $json['version']);
    }

    public function test_get_html(): void
    {
        [$blog, $user, $post] = $this->setUpVariant();

        $this->consoleBlogApi('GET', $blog, "/post/{$post->getId()}/variant/content?format=html", user: $user);

        $this->assertResponseIsSuccessful();
        $content = $this->getJson()['content'];
        $this->assertIsString($content);
        $this->assertStringContainsString('<strong>world</strong>', $content);
    }

    public function test_get_json(): void
    {
        [$blog, $user, $post] = $this->setUpVariant();

        $this->consoleBlogApi('GET', $blog, "/post/{$post->getId()}/variant/content?format=json", user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame(self::JSON, $this->getJson()['content']);
    }

    public function test_get_unknown_language(): void
    {
        [$blog, $user, $post] = $this->setUpVariant();

        $this->consoleBlogApi('GET', $blog, "/post/{$post->getId()}/variant/content?language_code=xx", user: $user);

        $this->assertResponseFailed(422, 'Language not found');
    }

    public function test_put_markdown(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PUT', $blog, "/post/{$post->getId()}/variant/content", [
            'content' => "# Title\n\nSome *text*",
        ], user: $user);

        $this->assertResponseIsSuccessful();
        refresh($variant);
        $this->assertStringContainsString('heading', (string)$variant->getContentUnsaved());
        $this->assertStringContainsString('Some ', (string)$variant->getContentUnsaved());
        $this->assertSame(0, $variant->getDocumentVersion());
    }

    public function test_put_html(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PUT', $blog, "/post/{$post->getId()}/variant/content", [
            'format' => 'html',
            'content' => '<p>Hi <em>there</em></p>',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        refresh($variant);
        $this->assertStringContainsString('Hi ', (string)$variant->getContentUnsaved());
    }

    public function test_put_json(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();
        $new = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"New"}]}]}';

        $this->consoleBlogApi('PUT', $blog, "/post/{$post->getId()}/variant/content", [
            'format' => 'json',
            'content' => $new,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        refresh($variant);
        $this->assertStringContainsString('"text":"New"', (string)$variant->getContentUnsaved());
    }

    public function test_put_invalid_json(): void
    {
        [$blog, $user, $post] = $this->setUpVariant();

        $this->consoleBlogApi('PUT', $blog, "/post/{$post->getId()}/variant/content", [
            'format' => 'json',
            'content' => '{"type":"nope"}',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_put_version_mismatch(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PUT', $blog, "/post/{$post->getId()}/variant/content", [
            'content' => 'x',
            'version' => 2,
        ], user: $user);

        $this->assertResponseStatusCodeSame(409);
        refresh($variant);
        $this->assertSame(self::JSON, $variant->getContentUnsaved());
    }

    public function test_put_version_match(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PUT', $blog, "/post/{$post->getId()}/variant/content", [
            'content' => 'matched',
            'version' => 3,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        refresh($variant);
        $this->assertStringContainsString('matched', (string)$variant->getContentUnsaved());
    }
}
