<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Api\Console\Object\PostObject;
use App\Api\Console\Object\PostObjectFactory;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserStatus;
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
class GetPostTest extends ApiTestCase
{
    public function test_returns_post_with_variants_tags_and_authors(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-get']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);

        $post = PostFactory::createOne(['blog' => $blog, 'is_featured' => true]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language, 'title' => 'Hello World', 'status' => PostVariantStatus::PUBLISHED]);

        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $language]);
        $post->getTags()->add($tag);
        $post->getAuthors()->add($user);
        $this->getEm()->flush();

        $this->consoleBlogApi('GET', $blog, '/post/' . $post->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertSame($post->getId(), $json['id']);
        $this->assertTrue($json['is_featured']);
        $this->assertIsArray($json['variant_statuses']);
        $this->assertCount(1, $json['variant_statuses']);
        $this->assertIsArray($json['variant_statuses'][0]);
        $this->assertSame('published', $json['variant_statuses'][0]['status']);
        $this->assertIsArray($json['tags']);
        $this->assertCount(1, $json['tags']);
        $this->assertIsArray($json['authors']);
        $this->assertCount(1, $json['authors']);
    }

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
