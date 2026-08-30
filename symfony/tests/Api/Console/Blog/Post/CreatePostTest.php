<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Api\Console\Object\PostObject;
use App\Api\Console\Object\PostObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Post\PostService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
#[CoversClass(PostObject::class)]
#[CoversClass(PostObjectFactory::class)]
class CreatePostTest extends ApiTestCase
{
    public function test_creates_a_post_with_primary_language_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-create']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $lang = LanguageFactory::createOnePrimaryFor($blog);

        $this->consoleBlogApi('POST', $blog, '/post', [], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();

        $this->assertFalse($json['is_page']);
        $this->assertFalse($json['is_featured']);
        $this->assertIsArray($json['variants']);
        $this->assertCount(1, $json['variants']);
        $this->assertIsArray($json['variants'][0]);
        $this->assertSame('draft', $json['variants'][0]['status']);

        $posts = $this->getEm()->getRepository(Post::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $posts);

        $variants = $this->getEm()->getRepository(PostVariant::class)->findBy(['post' => $posts[0]]);
        $this->assertCount(1, $variants);
        $this->assertSame($lang->getId(), $variants[0]->getLanguage()->getId());
    }

    public function test_creates_a_page(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-create-page']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        LanguageFactory::createOnePrimaryFor($blog);

        $this->consoleBlogApi('POST', $blog, '/post', ['is_page' => true], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertTrue($json['is_page']);

        $posts = $this->getEm()->getRepository(Post::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $posts);
        $this->assertTrue($posts[0]->isPage());
    }

    public function test_adds_creator_as_author(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-create-author']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE, 'hyvor_user_id' => 42]);
        LanguageFactory::createOnePrimaryFor($blog);

        $this->consoleBlogApi('POST', $blog, '/post', [], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertIsArray($json['authors']);
        $this->assertCount(1, $json['authors']);

        $post = $this->getEm()->getRepository(Post::class)->findOneBy(['blog' => $blog]);
        $this->assertNotNull($post);
        $this->assertCount(1, $post->getAuthors());

        $author = $post->getAuthors()->first();
        $this->assertNotFalse($author);
        $this->assertSame(42, $author->getHyvorUserId());
    }
}
