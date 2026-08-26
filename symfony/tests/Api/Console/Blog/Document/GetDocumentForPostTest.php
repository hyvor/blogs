<?php

namespace App\Tests\Api\Console\Blog\Document;

use App\Api\Console\Controller\PostController;
use App\Api\Console\Object\PostObject;
use App\Api\Console\Object\PostObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Service\Post\Document\DocumentService;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
#[CoversClass(PostObject::class)]
#[CoversClass(PostObjectFactory::class)]
#[CoversClass(DocumentService::class)]
class GetDocumentForPostTest extends ApiTestCase
{
    public function test_returns_post_with_variants_tags_and_authors(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-get']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post = PostFactory::createOne(['blog' => $blog, 'is_featured' => true]);
        $variant = PostVariantFactory::createOnePublishedFor(
            $post,
            [
                'content_unsaved_version' => 1,
                'content_unsaved' => 'unsaved content',
            ],
            language: $language);

        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $language]);
        $post->getTags()->add($tag);
        $post->getAuthors()->add($user);
        $this->getEm()->flush();

        $this->consoleBlogApi('GET', $blog, '/documents/post?post_id=' . $post->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $postJson = $json['post'];

        $this->assertSame($post->getId(), $postJson['id']);
        $this->assertTrue($postJson['is_featured']);
        $this->assertIsArray($postJson['variants']);
        $this->assertCount(1, $postJson['variants']);
        $this->assertIsArray($postJson['variants'][0]);
        $this->assertSame('published', $postJson['variants'][0]['status']);
        $this->assertIsArray($postJson['tags']);
        $this->assertCount(1, $postJson['tags']);
        $this->assertIsArray($postJson['authors']);
        $this->assertCount(1, $postJson['authors']);

        $variantJson = $json['variant'];
        $this->assertSame($language->getId(), $variantJson['language_id']);
        $this->assertSame($variant->getId(), $variantJson['id']);

        $documentJson = $json['document'];
        $this->assertSame(1, $documentJson['checkpoint_version']);
        $this->assertSame('unsaved content', $documentJson['checkpoint_content']);
        $this->assertIsString($documentJson['mercure_token']);
    }

    public function test_when_post_not_found(): void;
    public function test_when_language_not_found(): void;
    public function test_when_variant_not_found(): void;
    public function test_with_another_variant(): void; // secondary variant


    public function test_returns_404_for_wrong_blog(): void
    {
        $blog1 = BlogFactory::createOne(['subdomain' => 'post-get-blog1']);
        $blog2 = BlogFactory::createOne(['subdomain' => 'post-get-blog2']);
        $user1 = UserFactory::createOne(['blog' => $blog1, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog2);

        $post = PostFactory::createOne(['blog' => $blog2]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('GET', $blog1, '/post/' . $post->getId(), user: $user1);

        $this->assertResponseFailed(404, 'Entity does not belong to blog');
    }
}
