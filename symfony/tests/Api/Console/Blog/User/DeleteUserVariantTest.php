<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Entity\UserVariant;
use App\Service\User\Event\UserVariantDeletedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
class DeleteUserVariantTest extends ApiTestCase
{
    public function test_deletes_a_user_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-user-variant']);
        $primary = LanguageFactory::createOnePrimaryFor($blog);
        $secondary = LanguageFactory::createOneFor($blog, ['code' => 'fr']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog]);
        UserVariantFactory::createOne(['user' => $user, 'language' => $primary]);
        $variant2 = UserVariantFactory::createOne(['user' => $user, 'language' => $secondary]);
        $variant2Id = $variant2->getId();

        $this->consoleBlogApi('DELETE', $blog, '/user/' . $user->getId() . '/variant', [
            'language_id' => $secondary->getId(),
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertNull($this->getEm()->getRepository(UserVariant::class)->find($variant2Id));

        $this->getEd()->assertDispatched(UserVariantDeletedEvent::class);
    }

    public function test_does_not_delete_primary_language_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'delete-user-variant-primary']);
        $primary = LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog]);
        UserVariantFactory::createOne(['user' => $user, 'language' => $primary]);

        $this->consoleBlogApi('DELETE', $blog, '/user/' . $user->getId() . '/variant', [
            'language_id' => $primary->getId(),
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
    }
}
