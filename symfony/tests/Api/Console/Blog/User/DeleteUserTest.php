<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Entity\Enum\UserRole;
use App\Entity\UserVariant;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserVariantDeletedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
class DeleteUserTest extends ApiTestCase
{

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
}
