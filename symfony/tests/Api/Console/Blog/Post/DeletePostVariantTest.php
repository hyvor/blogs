<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\PostVariant;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
class DeletePostVariantTest extends ApiTestCase
{
    public function test_deletes_secondary_language_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-delete']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $primaryLang = LanguageFactory::createOnePrimaryFor($blog);
        $secondaryLang = LanguageFactory::createOneFor($blog, ['code' => 'fr']);

        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $primaryLang]);
        $variant2 = PostVariantFactory::createOne(['post' => $post, 'language' => $secondaryLang]);
        $variant2Id = $variant2->getId();

        $this->consoleBlogApi('DELETE', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $secondaryLang->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();

        $this->assertNull($this->getEm()->getRepository(PostVariant::class)->find($variant2Id));
    }

    public function test_cannot_delete_primary_language_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-delete-primary']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $primaryLang = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $primaryLang]);

        $this->consoleBlogApi('DELETE', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $primaryLang->getId(),
        ], user: $user);

        $this->assertResponseFailed(422, 'Primary language variant cannot be deleted. Delete the post instead');
    }

    public function test_returns_404_if_variant_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-delete-nf']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $primaryLang = LanguageFactory::createOnePrimaryFor($blog);
        $secondaryLang = LanguageFactory::createOneFor($blog, ['code' => 'de']);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $primaryLang]);

        $this->consoleBlogApi('DELETE', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $secondaryLang->getId(),
        ], user: $user);

        $this->assertResponseFailed(404, 'Variant not found');
    }
}
