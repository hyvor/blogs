<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Entity\Blog;
use App\Entity\Enum\UserRole;
use App\Entity\Enum\UserStatus;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Service\User\Event\UserUpdatedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\HyvorPostFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Fake\HyvorPostServiceFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
class UpdateUserTest extends ApiTestCase
{
    private function connectHyvorPost(Blog $blog): HyvorPostServiceFake
    {
        $fake = new HyvorPostServiceFake($this->getEm());
        $this->getContainer()->set(HyvorPostService::class, $fake);

        HyvorPostFactory::createOne(['blog' => $blog, 'newsletter_id' => 4444]);

        return $fake;
    }

    public function test_updates_a_user(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER]);

        $updates = [
            'hyvor_user_id' => 10,
            'role' => 'editor',
            'status' => UserStatus::ACTIVE->value,
            'slug' => 'i-am-hyvor',
            'email' => 'email@hyvor.com',
            'website_url' => 'https://example.com/website',
            'picture_url' => 'https://example.com/picture',
            'social_facebook' => 'https://example.com/facebook',
            'social_twitter' => 'https://example.com/twitter',
            'social_linkedin' => 'https://example.com/linkedin',
            'social_youtube' => 'https://example.com/youtube',
            'social_tiktok' => 'https://example.com/tiktok',
            'social_instagram' => 'https://example.com/instagram',
            'social_github' => 'https://example.com/github',
        ];

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user->getId(), $updates, user: $owner);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        foreach ($updates as $key => $value) {
            $this->assertSame($value, $json[$key], "key: $key");
        }

        $this->getEd()->assertDispatched(UserUpdatedEvent::class);
    }

    public function test_does_not_update_the_role_of_the_admin(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-owner-role']);
        $owner = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $owner->getId(), [
            'role' => 'editor',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'cannot update the role of the admin',
            (string)$this->client->getResponse()->getContent(),
        );
    }

    public function test_does_not_update_the_role_to_admin(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-to-owner']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR]);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user->getId(), [
            'role' => 'admin',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'cannot update the role to admin',
            (string)$this->client->getResponse()->getContent(),
        );
    }

    public function test_does_not_update_the_status_of_the_admin(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-owner-status']);
        $owner = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $owner->getId(), [
            'status' => UserStatus::BLOCKED,
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'cannot update the status of the admin',
            (string)$this->client->getResponse()->getContent(),
        );
    }

    public function test_returns_error_when_updating_to_existing_slug(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-slug']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        UserFactory::createOne(['blog' => $blog, 'slug' => 'test']);
        $user2 = UserFactory::createOne(['blog' => $blog, 'slug' => 'test2']);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user2->getId(), [
            'slug' => 'test',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'Slug already taken',
            (string)$this->client->getResponse()->getContent(),
        );
    }

    public function test_creates_hyvor_post_user_when_role_changes_to_allowed(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-hp-to-allowed']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER, 'hyvor_user_id' => 9101]);
        $fake = $this->connectHyvorPost($blog);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user->getId(), [
            'role' => 'editor',
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $fake->addedUsers);
        $this->assertSame(9101, $fake->addedUsers[0]['hyvorUserId']);
        $this->assertCount(0, $fake->removedUsers);
    }

    public function test_removes_hyvor_post_user_when_role_changes_to_non_allowed(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-hp-to-non-allowed']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR, 'hyvor_user_id' => 9102]);
        $fake = $this->connectHyvorPost($blog);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user->getId(), [
            'role' => 'writer',
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $fake->removedUsers);
        $this->assertSame(9102, $fake->removedUsers[0]['hyvorUserId']);
        $this->assertCount(0, $fake->addedUsers);
    }

    public function test_does_not_sync_hyvor_post_when_role_unchanged(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-hp-unchanged']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR, 'hyvor_user_id' => 9103]);
        $fake = $this->connectHyvorPost($blog);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user->getId(), [
            'slug' => 'a-new-slug',
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $fake->addedUsers);
        $this->assertCount(0, $fake->removedUsers);
    }

    public function test_does_not_sync_hyvor_post_between_two_non_allowed_roles(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-hp-non-allowed-to-non-allowed']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER, 'hyvor_user_id' => 9104]);
        $fake = $this->connectHyvorPost($blog);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user->getId(), [
            'role' => 'contributor',
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $fake->addedUsers);
        $this->assertCount(0, $fake->removedUsers);
    }

    public function test_does_not_sync_hyvor_post_when_not_connected(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-hp-unconnected']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER, 'hyvor_user_id' => 9105]);

        $fake = new HyvorPostServiceFake($this->getEm());
        $this->getContainer()->set(HyvorPostService::class, $fake);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user->getId(), [
            'role' => 'editor',
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $fake->addedUsers);
        $this->assertCount(0, $fake->removedUsers);
    }
}
