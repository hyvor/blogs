<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Api\Console\Object\UserObjectFactory;
use App\Entity\Enum\UserRole;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserObjectFactory::class)]
class GetUsersTest extends ApiTestCase
{
    public function test_gets_users(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'get-users']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        for ($i = 0; $i < 3; $i++) {
            $user = UserFactory::createOne(['blog' => $blog]);
            UserVariantFactory::createOne(['user' => $user, 'language' => $language]);
        }
        UserVariantFactory::createOne(['user' => $owner, 'language' => $language]);

        $this->consoleBlogApi('GET', $blog, '/users', user: $owner);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame(4, $json['used']);
        $users = $json['users'];
        $this->assertIsArray($users);
        $this->assertCount(4, $users);
        $this->assertIsArray($users[0]);
        $this->assertArrayHasKey('id', $users[0]);
        $this->assertArrayHasKey('role', $users[0]);
        $this->assertArrayHasKey('status', $users[0]);
        $this->assertArrayHasKey('variants', $users[0]);
    }

    public function test_searches_users(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'search-users']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        UserVariantFactory::createOne(['user' => $owner, 'language' => $language]);

        $name = 'Thisisname';
        $user = UserFactory::createOne(['blog' => $blog]);
        UserVariantFactory::createOne(['user' => $user, 'language' => $language, 'name' => $name]);

        $user2 = UserFactory::createOne(['blog' => $blog]);
        UserVariantFactory::createOne(['user' => $user2, 'language' => $language, 'name' => 'Another name']);

        $this->consoleBlogApi('GET', $blog, '/users?search=Thisis', user: $owner);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $users = $json['users'];
        $this->assertIsArray($users);
        $this->assertCount(1, $users);
        $this->assertIsArray($users[0]);
        $this->assertIsArray($users[0]['variants']);
        $this->assertIsArray($users[0]['variants'][0]);
        $this->assertSame($name, $users[0]['variants'][0]['name']);
        // usage is the total, not affected by search
        $this->assertSame(3, $json['used']);
    }

    public function test_gets_users_usage(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'get-users-usage']);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);
        UserFactory::createMany(2, ['blog' => $blog]);
        UserFactory::createOne(['blog' => BlogFactory::createOne()]);

        $this->consoleBlogApi('GET', $blog, '/users', user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertSame(3, $this->getJson()['used']);
    }
}
