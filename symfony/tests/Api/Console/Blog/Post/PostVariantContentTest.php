<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Blog;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\User;
use App\Service\Post\Content\PostContentConverter;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(PostController::class)]
#[CoversClass(PostContentConverter::class)]
class PostVariantContentTest extends ApiTestCase
{
    private const string JSON = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hello "},{"type":"text","marks":[{"type":"strong"}],"text":"world"}]}]}';

    /** @return array{0: Blog, 1: User, 2: Post, 3: PostVariant} */
    private function setUpVariant(): array
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);
        $variant = PostVariantFactory::createOneFor($post, [
            'language' => $language,
            'content_unsaved' => self::JSON,
            'document_version' => 3,
            'title' => 'Old title',
        ]);
        return [$blog, $user, $post, $variant];
    }

    private function contentOf(string $key): string
    {
        $document = $this->getJson()['document'];
        $this->assertIsArray($document);
        $value = $document[$key];
        $this->assertIsString($value);
        return $value;
    }

    public function test_get_markdown_by_default(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('GET', $blog, "/post/{$post->getId()}/variant", user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('markdown', $this->contentOf('format'));
        $this->assertSame('Hello **world**', trim($this->contentOf('content')));
        $this->assertSame(3, $json['document']['version']); // @phpstan-ignore offsetAccess.nonOffsetAccessible
        $this->assertSame($variant->getId(), $json['variant']['id']); // @phpstan-ignore offsetAccess.nonOffsetAccessible
    }

    public function test_get_html(): void
    {
        [$blog, $user, $post] = $this->setUpVariant();

        $this->consoleBlogApi('GET', $blog, "/post/{$post->getId()}/variant?format=html", user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('<strong>world</strong>', $this->contentOf('content'));
    }

    public function test_get_json(): void
    {
        [$blog, $user, $post] = $this->setUpVariant();

        $this->consoleBlogApi('GET', $blog, "/post/{$post->getId()}/variant?format=json", user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame(self::JSON, $this->contentOf('content'));
    }

    public function test_get_unknown_language(): void
    {
        [$blog, $user, $post] = $this->setUpVariant();

        $this->consoleBlogApi('GET', $blog, "/post/{$post->getId()}/variant?language_id=999999", user: $user);

        $this->assertResponseFailed(422, 'Language not found');
    }

    public function test_get_variant_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $this->consoleBlogApi('GET', $blog, "/post/{$post->getId()}/variant", user: $user);

        $this->assertResponseFailed(404, 'Variant not found');
    }

    public function test_patch_markdown_content_with_other_fields(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PATCH', $blog, "/post/{$post->getId()}/variant", [
            'language_id' => $variant->getLanguage()->getId(),
            'content' => "# Title\n\nSome *text*",
            'title' => 'New title',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        refresh($variant);
        $this->assertStringContainsString('heading', (string)$variant->getContentUnsaved());
        $this->assertSame('New title', $variant->getTitle());
        $this->assertSame(0, $variant->getDocumentVersion());
    }

    public function test_patch_html_content(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PATCH', $blog, "/post/{$post->getId()}/variant", [
            'language_id' => $variant->getLanguage()->getId(),
            'format' => 'html',
            'content' => '<p>Hi <em>there</em></p>',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        refresh($variant);
        $this->assertStringContainsString('Hi ', (string)$variant->getContentUnsaved());
    }

    public function test_patch_json_content(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PATCH', $blog, "/post/{$post->getId()}/variant", [
            'language_id' => $variant->getLanguage()->getId(),
            'format' => 'json',
            'content' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"New"}]}]}',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        refresh($variant);
        $this->assertStringContainsString('"text":"New"', (string)$variant->getContentUnsaved());
    }

    public function test_patch_invalid_content(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PATCH', $blog, "/post/{$post->getId()}/variant", [
            'language_id' => $variant->getLanguage()->getId(),
            'format' => 'json',
            'content' => '{"type":"nope"}',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_patch_base_version_conflict_updates_nothing(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PATCH', $blog, "/post/{$post->getId()}/variant", [
            'language_id' => $variant->getLanguage()->getId(),
            'content' => 'x',
            'title' => 'Should not apply',
            'base_version' => 2,
        ], user: $user);

        $this->assertResponseStatusCodeSame(409);
        refresh($variant);
        $this->assertSame(self::JSON, $variant->getContentUnsaved());
        $this->assertSame('Old title', $variant->getTitle());
    }

    public function test_patch_base_version_match(): void
    {
        [$blog, $user, $post, $variant] = $this->setUpVariant();

        $this->consoleBlogApi('PATCH', $blog, "/post/{$post->getId()}/variant", [
            'language_id' => $variant->getLanguage()->getId(),
            'content' => 'matched',
            'base_version' => 3,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        refresh($variant);
        $this->assertStringContainsString('matched', (string)$variant->getContentUnsaved());
    }
}
