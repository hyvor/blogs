<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\UserStatus;
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
class GetPagesTest extends ApiTestCase
{
    public function test_fetches_pages_only(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'pages-get']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $page = PostFactory::createOne(['blog' => $blog, 'is_page' => true]);
        PostVariantFactory::createOne(['post' => $page, 'language' => $language]);

        $post = PostFactory::createOne(['blog' => $blog, 'is_page' => false]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        // other blog page
        $otherBlog = BlogFactory::createOne(['subdomain' => 'pages-get-other']);
        $otherBlogPage = PostFactory::createOne(['blog' => $otherBlog, 'is_page' => true]);
        PostVariantFactory::createOne(['post' => $otherBlogPage, 'language' => $language]);

        $this->consoleBlogApi('GET', $blog, '/pages', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertTrue($json[0]['is_page']);
    }
}
