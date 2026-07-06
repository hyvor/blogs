<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\PostVariantStatus;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
class UnpublishPostVariantTest extends ApiTestCase
{
    public function test_language_not_found(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/unpublish', [
            'language_id' => 9999,
        ], user: $user);

        $this->assertResponseFailed(422, 'Language not found');
    }

    public function test_variant_not_found(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $user = UserFactory::createOne(['blog' => $blog]);
        $post = PostFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/unpublish', [
            'language_id' => $blog->getLanguages()->first()->getId(),
        ], user: $user);

        $this->assertResponseFailed(404, 'Variant not found');
    }

    public function test_unpublishes_published_variant(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'slug' => 'my-post',
        ]);

        $response = $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/unpublish', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string)$response->getContent(), true);
        $this->assertSame('draft', $data['status']);
    }

    public function test_unschedules_scheduled_variant(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        RouteFactory::createDefaultsFor($blog);
        $user = UserFactory::createOne(['blog' => $blog]);
        $language = $blog->getLanguages()->first();
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::SCHEDULED,
            'slug' => 'my-post',
        ]);

        $response = $this->consoleBlogApi('POST', $blog, '/post/' . $post->getId() . '/variant/unpublish', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string)$response->getContent(), true);
        $this->assertSame('draft', $data['status']);
    }
}
