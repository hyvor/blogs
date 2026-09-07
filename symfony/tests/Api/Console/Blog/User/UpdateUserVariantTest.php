<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Service\User\Event\UserVariantUpdatedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserVariantUpdatedEvent::class)]
class UpdateUserVariantTest extends ApiTestCase
{
    public function test_updates_user_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'update-user-variant']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog]);
        UserVariantFactory::createOne(['user' => $user, 'language' => $language]);

        $this->consoleBlogApi('PATCH', $blog, '/user/' . $user->getId() . '/variant', [
            'language_id' => $language->getId(),
            'name' => 'Hey',
            'bio' => 'I am hey',
            'location' => 'France',
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('Hey', $json['name']);
        $this->assertSame('I am hey', $json['bio']);
        $this->assertSame('France', $json['location']);

        $this->getEd()->assertDispatched(UserVariantUpdatedEvent::class);
    }
}
