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
class UpdateBlogVariantTest extends ApiTestCase
{
    public function test_validates(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-var-update-validate']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/blog/variant', [
            'language_id' => 2,
            'name' => false,
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_updates_name(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-var-update-name']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        BlogVariantFactory::createOne(['blog' => $blog, 'language' => $language]);

        $name = 'Name';

        $this->consoleBlogApi('PATCH', $blog, '/blog/variant', [
            'language_id' => $language->getId(),
            'name' => $name,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($name, $json['name']);
    }

    public function test_updates_description(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'blog-var-update-desc']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        BlogVariantFactory::createOne(['blog' => $blog, 'language' => $language]);

        $description = 'Hello world';

        $this->consoleBlogApi('PATCH', $blog, '/blog/variant', [
            'language_id' => $language->getId(),
            'description' => $description,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($description, $json['description']);
    }
}
