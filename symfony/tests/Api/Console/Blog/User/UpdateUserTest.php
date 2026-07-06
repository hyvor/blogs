<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Entity\Enum\UserRole;
use App\Entity\Enum\UserStatus;
use App\Service\User\Event\UserUpdatedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
class UpdateUserTest extends ApiTestCase
{
    public function test_updates_a_user(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $updates = [
            'hyvor_user_id' => 10,
            'role' => 'editor',
            'status' => UserStatus::ACTIVE,
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

    public function test_does_not_update_the_role_of_the_owner(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-owner-role']);
        $owner = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::OWNER]);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $owner->getId(), [
            'role' => 'admin',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'cannot update the role of the owner',
            (string)$this->client->getResponse()->getContent(),
        );
    }

    public function test_does_not_update_the_role_to_owner(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-to-owner']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user->getId(), [
            'role' => 'owner',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'cannot update the role to owner',
            (string)$this->client->getResponse()->getContent(),
        );
    }

    public function test_does_not_update_the_status_of_the_owner(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-owner-status']);
        $owner = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::OWNER]);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $owner->getId(), [
            'status' => UserStatus::BLOCKED,
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'cannot update the status of the owner',
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
}
