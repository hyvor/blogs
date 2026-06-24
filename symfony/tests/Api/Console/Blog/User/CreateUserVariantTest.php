<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Service\User\Event\UserVariantCreatedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
class CreateUserVariantTest extends ApiTestCase
{
    public function test_creates_a_user_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user-variant']);
        $language1 = LanguageFactory::createOnePrimaryFor($blog);
        $language2 = LanguageFactory::createOneFor($blog, ['code' => 'fr']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/user/' . $user->getId() . '/variant', [
            'language_id' => $language2->getId(),
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('bio', $json);
        $this->assertSame($language2->getId(), $json['language_id']);

        $this->getEd()->assertDispatched(UserVariantCreatedEvent::class);
    }
}
