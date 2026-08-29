<?php

namespace App\Tests\Api\Console\Blog\Post;

use App\Api\Console\Controller\PostController;
use App\Entity\Enum\UserStatus;
use App\Service\Limit;
use App\Service\Post\Event\PostAuthorsChangedEvent;
use App\Service\Post\PostService;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(PostController::class)]
#[CoversClass(PostService::class)]
#[CoversClass(UserService::class)]
#[CoversClass(PostAuthorsChangedEvent::class)]
class UpdatePostAuthorsTest extends ApiTestCase
{
    public function test_updates_post_authors(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-authors-update']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $author2 = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/authors', [
            'ids' => [$user->getId(), $author2->getId()],
        ], user: $user);

        $this->assertResponseIsSuccessful();

        $this->getEm()->refresh($post);
        $this->assertCount(2, $post->getAuthors());

        $event = $this->getEd()->getFirstEvent(PostAuthorsChangedEvent::class);
        $this->assertSame($post->getId(), $event->post->getId());
        $this->assertCount(0, $event->oldAuthors);
        $this->assertCount(2, $event->newAuthors);
    }

    public function test_replaces_post_authors(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-authors-replace']);
        $user1 = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $user2 = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);
        $post->getAuthors()->add($user1);
        $this->getEm()->flush();

        // Now, replace with user2
        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/authors', [
            'ids' => [$user2->getId()],
        ], user: $user1);

        $this->assertResponseIsSuccessful();

        refresh($post);
        $this->assertCount(1, $post->getAuthors());
        $firstAuthor = $post->getAuthors()[0];
        $this->assertNotNull($firstAuthor);
        $this->assertSame($user2->getId(), $firstAuthor->getId());

        $event = $this->getEd()->getFirstEvent(PostAuthorsChangedEvent::class);
        $this->assertSame($post->getId(), $event->post->getId());
        $this->assertCount(1, $event->oldAuthors);
        $this->assertSame($user1->getId(), $event->oldAuthors[0]->getId());
        $this->assertCount(1, $event->newAuthors);
        $this->assertSame($user2->getId(), $event->newAuthors[0]->getId());
    }

    public function test_fails_if_author_belongs_to_different_blog(): void
    {
        $blog1 = BlogFactory::createOne(['subdomain' => 'post-authors-blog1']);
        $blog2 = BlogFactory::createOne(['subdomain' => 'post-authors-blog2']);
        $user1 = UserFactory::createOne(['blog' => $blog1, 'status' => UserStatus::ACTIVE]);
        $userFromBlog2 = UserFactory::createOne(['blog' => $blog2, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog1);
        $post = PostFactory::createOne(['blog' => $blog1]);
        PostVariantFactory::createOne(['post' => $post, 'language' => $language]);

        $this->consoleBlogApi('PATCH', $blog1, '/post/' . $post->getId() . '/authors', [
            'ids' => [$userFromBlog2->getId()],
        ], user: $user1);

        $this->assertResponseFailed(422, 'Some author IDs are invalid');
    }

    public function test_fails_if_more_than_max_authors(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'post-authors-limit']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $post = PostFactory::createOne(['blog' => $blog]);

        $authorIds = array_fill(0, Limit::MAX_AUTHORS_PER_POST + 1, 1);

        $this->consoleBlogApi('PATCH', $blog, '/post/' . $post->getId() . '/authors', [
            'ids' => $authorIds,
        ], user: $user);

        $this->assertResponseFailed(422, 'You can assign a maximum of ' . Limit::MAX_AUTHORS_PER_POST . ' authors to a post.');
    }
}
