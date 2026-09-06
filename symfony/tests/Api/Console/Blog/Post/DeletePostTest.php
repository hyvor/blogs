<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\UserStatus;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Post\Event\PostDeletedEvent;
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
#[CoversClass(PostDeletedEvent::class)]
class DeletePostTest extends ApiTestCase
{
    public function test_deletes_post(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-delete']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $postId = $post->getId();

        $this->consoleBlogApi('DELETE', $blog, '/post/' . $postId, user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertNull($this->getEm()->getRepository(Post::class)->find($postId));
        $this->assertCount(0, $this->getEm()->getRepository(PostVariant::class)->findAll());

        $this->getEd()->assertDispatched(PostDeletedEvent::class);
    }

    public function test_returns_404_for_wrong_blog(): void
    {
        $blog1 = BlogFactory::createOne(['subdomain' => 'post-delete-b1']);
        $blog2 = BlogFactory::createOne(['subdomain' => 'post-delete-b2']);
        $user1 = UserFactory::createOne(['blog' => $blog1, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog2);
        $post = PostFactory::createOne(['blog' => $blog2]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('DELETE', $blog1, '/post/' . $post->getId(), user: $user1);

        $this->assertResponseFailed(404, 'Entity does not belong to blog');
    }
}
