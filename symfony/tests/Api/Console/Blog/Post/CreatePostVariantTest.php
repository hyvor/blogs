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
class CreatePostVariantTest extends ApiTestCase
{
    public function test_creates_variant_for_secondary_language(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-create']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $primaryLang = LanguageFactory::createOnePrimaryFor($blog);
        $secondaryLang = LanguageFactory::createOneFor($blog, ['code' => 'fr']);

        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $primaryLang]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $secondaryLang->getId(),
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('language_id', $json);
        $this->assertSame($secondaryLang->getId(), $json['language_id']);
        $this->assertSame('draft', $json['status']);

        $variants = $this->getEm()->getRepository(PostVariant::class)->findBy(['post' => $post]);
        $this->assertCount(2, $variants);
    }

    public function test_fails_if_variant_already_exists(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-create-dup']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_fails_if_language_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-variant-create-nf']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant', [
            'language_id' => 99999,
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }
}
