<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Entity\Blog;
use App\Entity\Enum\UserRole;
use App\Entity\UserVariant;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserVariantDeletedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use App\Tests\Fake\HyvorPostServiceFake;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
class DeleteUserTest extends ApiTestCase
{
    private function connectHyvorPost(Blog $blog): HyvorPostServiceFake
    {
        $fake = new HyvorPostServiceFake($this->getEm());
        $this->getContainer()->set(HyvorPostService::class, $fake);

        HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 4343]);

        return $fake;
    }

    public function test_deletes_the_user_and_its_variants(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-user']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR]);

        for ($i = 0; $i < 3; $i++) {
            UserVariantFactory::createOne(['user' => $user, 'language' => LanguageFactory::createOneFor($blog)]);
        }

        $post = PostFactory::createOne();
        $post->getAuthors()->add($user);
        $this->getEm()->flush();

        $userId = $user->getId();
        $this->assertCount(3, $this->getEm()->getRepository(UserVariant::class)->findBy(['user' => $userId]));

        $this->consoleBlogApi('DELETE', $blog, '/user/' . $userId, user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertNull($this->getEm()->getRepository(\App\Entity\User::class)->find($userId));
        $this->assertCount(0, $this->getEm()->getRepository(UserVariant::class)->findBy(['user' => $userId]));

        $this->getEd()->assertDispatched(UserDeletedEvent::class);
        $this->getEd()->assertDispatchedCount(UserVariantDeletedEvent::class, 3);

        refresh($post);
        $this->assertCount(0, $post->getAuthors());
    }

    public function test_cannot_delete_admin(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-user-owner']);
        $owner = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $this->consoleBlogApi('DELETE', $blog, '/user/' . $owner->getId(), user: $owner);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'Cannot delete the admin',
            (string)$this->client->getResponse()->getContent(),
        );

        $this->assertNotNull($this->getEm()->getRepository(\App\Entity\User::class)->find($owner->getId()));
    }

    public function test_removes_hyvor_post_user_for_editor_role(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-user-hp-editor']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR, 'hyvor_user_id' => 9001]);
        $fake = $this->connectHyvorPost($blog);

        $this->consoleBlogApi('DELETE', $blog, '/user/' . $user->getId(), user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $fake->removedUsers);
        $this->assertSame(9001, $fake->removedUsers[0]['hyvorUserId']);
        $this->assertSame(4343, $fake->removedUsers[0]['newsletterId']);
    }

    public function test_does_not_remove_hyvor_post_user_for_writer_role(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-user-hp-writer']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER, 'hyvor_user_id' => 9002]);
        $fake = $this->connectHyvorPost($blog);

        $this->consoleBlogApi('DELETE', $blog, '/user/' . $user->getId(), user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $fake->removedUsers);
    }

    public function test_does_not_remove_hyvor_post_user_when_not_connected(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-user-hp-unconnected']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR, 'hyvor_user_id' => 9003]);

        $fake = new HyvorPostServiceFake($this->getEm());
        $this->getContainer()->set(HyvorPostService::class, $fake);

        $this->consoleBlogApi('DELETE', $blog, '/user/' . $user->getId(), user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $fake->removedUsers);
    }

    public function test_does_not_remove_hyvor_post_user_for_guest(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-user-hp-guest']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR, 'hyvor_user_id' => null]);
        $fake = $this->connectHyvorPost($blog);

        $this->consoleBlogApi('DELETE', $blog, '/user/' . $user->getId(), user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $fake->removedUsers);
    }
}
