<?php

namespace App\Tests\Api\Console\Blog;

use App\Api\Console\Controller\BlogController;
use App\Api\Console\Object\BlogVariantObject;
use App\Entity\Enum\UserStatus;
use App\Service\Blog\BlogService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogService::class)]
#[CoversClass(BlogVariantObject::class)]
class CreateBlogVariantTest extends ApiTestCase
{
    public function test_validates(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-var-create-validate']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('POST', $blog, '/blog/variant', [
            'language_id' => null,
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_does_not_create_a_blog_variant_if_it_already_exists(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-var-create-exists']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        BlogVariantFactory::createOne(['blog' => $blog, 'language' => $language]);

        $this->consoleBlogApi('POST', $blog, '/blog/variant', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseFailed(422, 'Variant already there');
    }

    public function test_returns_an_error_if_the_language_is_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-var-create-nolang']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('POST', $blog, '/blog/variant', [
            'language_id' => 12321,
        ], user: $user);

        $this->assertResponseFailed(422, 'Language not found');
    }

    public function test_creates_a_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-var-create']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        LanguageFactory::createOnePrimaryFor($blog);
        $language = LanguageFactory::createOneFor($blog, ['code' => 'fr', 'name' => 'French']);

        $this->consoleBlogApi('POST', $blog, '/blog/variant', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame($language->getId(), $json['language_id']);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('description', $json);
    }
}
