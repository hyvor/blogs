<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserStatus;
use App\Entity\Post;
use App\Service\Post\Event\PostCreatedEvent;
use App\Service\Post\Event\PostVariantCreatedEvent;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
class ClonePostTest extends ApiTestCase
{
    public function test_post_cloning(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-clone']);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $originalPost = PostFactory::createOne([
            'blog' => $blog,
            'is_page' => true,
            'is_featured' => true,
            'featured_image_url' => 'https://example.com/image.jpg',
            'canonical_url' => 'https://example.com/canonical',
            'code_head' => '<meta name="test" content="head">',
            'code_foot' => '<script>console.log("foot")</script>',
        ]);
        $originalVariant = PostVariantFactory::createOne([
            'post' => $originalPost,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'original-slug',
            'title' => 'Original Title',
            'description' => 'Original Description',
            'content' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Original content"}]}]}',
            'content_unsaved' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Unsaved content"}]}]}',
            'seo_primary_keyword' => 'primary keyword',
            'seo_secondary_keywords' => ['keyword1', 'keyword2'],
            'link_analysis' => ['https://example.com' => 1],
            'published_at' => new \DateTimeImmutable(),
        ]);
        $originalPost->getVariants()->add($originalVariant);

        $tag1 = TagFactory::createOne(['blog' => $blog]);
        $tag2 = TagFactory::createOne(['blog' => $blog]);
        $author1 = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $author2 = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $originalPost->getTags()->add($tag1);
        $originalPost->getTags()->add($tag2);
        $originalPost->getAuthors()->add($author1);
        $originalPost->getAuthors()->add($author2);
        $this->getEm()->flush();

        $this->consoleBlogApi('POST', $blog, '/post/' . $originalPost->getId() . '/clone', user: $author1);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();

        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('variant_statuses', $json);
        $this->assertIsArray($json['variant_statuses']);
        $this->assertCount(1, $json['variant_statuses']);

        $clonedPost = $this->getEm()->getRepository(Post::class)->find($json['id']);
        $this->assertNotNull($clonedPost);

        $this->assertNotSame($originalPost->getId(), $clonedPost->getId());
        $this->assertSame($originalPost->isPage(), $clonedPost->isPage());
        $this->assertSame($originalPost->getFeaturedImageUrl(), $clonedPost->getFeaturedImageUrl());
        $this->assertSame($originalPost->getCanonicalUrl(), $clonedPost->getCanonicalUrl());
        $this->assertSame($originalPost->getCodeHead(), $clonedPost->getCodeHead());
        $this->assertSame($originalPost->getCodeFoot(), $clonedPost->getCodeFoot());

        $this->assertFalse($clonedPost->isFeatured());

        $clonedVariant = $clonedPost->getVariants()->first();
        $this->assertNotFalse($clonedVariant);

        $this->assertNull($clonedVariant->getPublishedAt());
        $this->assertSame('[Copy] Original Title', $clonedVariant->getTitle());
        $this->assertSame($originalVariant->getDescription(), $clonedVariant->getDescription());
        $this->assertNull($clonedVariant->getContent());
        $this->assertSame($originalVariant->getContentUnsaved(), $clonedVariant->getContentUnsaved());
        $this->assertSame($originalVariant->getSeoPrimaryKeyword(), $clonedVariant->getSeoPrimaryKeyword());
        $this->assertSame($originalVariant->getSeoSecondaryKeywords(), $clonedVariant->getSeoSecondaryKeywords());
        $this->assertSame($originalVariant->getLinkAnalysis(), $clonedVariant->getLinkAnalysis());

        $this->assertSame(PostVariantStatus::DRAFT, $clonedVariant->getStatus());
        $this->assertNull($clonedVariant->getSlug());

        $this->assertCount(2, $clonedPost->getTags());
        $clonedTagIds = array_map(fn($tag) => $tag->getId(), $clonedPost->getTags()->toArray());
        $originalTagIds = array_map(fn($tag) => $tag->getId(), $originalPost->getTags()->toArray());
        sort($clonedTagIds);
        sort($originalTagIds);
        $this->assertSame($originalTagIds, $clonedTagIds);

        $this->assertCount(2, $clonedPost->getAuthors());
        $clonedAuthorIds = array_map(fn($author) => $author->getId(), $clonedPost->getAuthors()->toArray());
        $originalAuthorIds = array_map(fn($author) => $author->getId(), $originalPost->getAuthors()->toArray());
        sort($clonedAuthorIds);
        sort($originalAuthorIds);
        $this->assertSame($originalAuthorIds, $clonedAuthorIds);

        $this->getEd()->assertDispatchedCount(PostVariantCreatedEvent::class, 0);
        $this->getEd()->assertDispatchedCount(PostCreatedEvent::class, 0);
    }
}
