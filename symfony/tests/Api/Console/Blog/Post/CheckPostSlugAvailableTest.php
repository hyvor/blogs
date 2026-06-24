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
class CheckPostSlugAvailableTest extends ApiTestCase
{
    public function test_returns_available_for_unique_slug(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-slug-avail']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'slug' => 'my-slug']);

        $this->consoleBlogApi('GET', $blog, '/post/' . $post->getId() . '/slug-available?language_id=' . $language->getId() . '&slug=new-unique-slug', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertTrue($json['available']);
    }

    public function test_returns_available_for_own_slug(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-slug-own']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'slug' => 'my-slug']);

        $this->consoleBlogApi('GET', $blog, '/post/' . $post->getId() . '/slug-available?language_id=' . $language->getId() . '&slug=my-slug', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertTrue($json['available']);
    }

    public function test_returns_unavailable_when_slug_taken_by_another_post(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-slug-taken']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post1 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post1, 'language' => $language, 'slug' => 'taken-slug']);

        $post2 = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post2, 'language' => $language, 'slug' => 'other-slug']);

        $this->consoleBlogApi('GET', $blog, '/post/' . $post2->getId() . '/slug-available?language_id=' . $language->getId() . '&slug=taken-slug', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertFalse($json['available']);
    }
}
