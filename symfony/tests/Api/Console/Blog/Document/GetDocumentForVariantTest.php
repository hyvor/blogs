<?php

namespace App\Tests\Api\Console\Blog\Document;

use App\Api\Console\Controller\DocumentsController;
use App\Entity\Enum\UserStatus;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DocumentsController::class)]
class GetDocumentForVariantTest extends ApiTestCase
{
    public function test_returns_the_current_version_and_content(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'variant-get']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post = PostFactory::createOneFor($blog);
        $variant = PostVariantFactory::createOneFor(
            $post,
            [
                'content_unsaved_version' => 3,
                'content_unsaved' => 'current content',
                'document_version' => 5,
            ],
            language: $language,
        );
        $this->getEm()->flush();

        $this->consoleBlogApi('GET', $blog, '/documents/variant?post_variant_id=' . $variant->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame(3, $json['version']);
        $this->assertSame('current content', $json['content']);
        $this->assertSame(5, $json['document_version']);
    }

    public function test_when_variant_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'variant-get-404']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('GET', $blog, '/documents/variant?post_variant_id=999999', user: $user);

        $this->assertResponseFailed(404, 'Post variant not found');
    }

    public function test_returns_404_for_wrong_blog(): void
    {
        $blog1 = BlogFactory::createOne(['subdomain' => 'variant-get-blog1']);
        $blog2 = BlogFactory::createOne(['subdomain' => 'variant-get-blog2']);
        $user1 = UserFactory::createOne(['blog' => $blog1, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog2);

        $post = PostFactory::createOneFor($blog2);
        $variant = PostVariantFactory::createOneFor($post, language: $language);

        $this->consoleBlogApi('GET', $blog1, '/documents/variant?post_variant_id=' . $variant->getId(), user: $user1);

        $this->assertResponseFailed(404, 'Post variant not found');
    }
}
