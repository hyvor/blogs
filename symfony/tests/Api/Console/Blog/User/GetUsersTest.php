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
        $owner = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::OWNER]);

        for ($i = 0; $i < 3; $i++) {
            $user = UserFactory::createOne(['blog' => $blog]);
            UserVariantFactory::createOne(['user' => $user, 'language' => $language]);
        }
        UserVariantFactory::createOne(['user' => $owner, 'language' => $language]);

        $this->consoleBlogApi('GET', $blog, '/users', user: $owner);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(4, $json);
        $this->assertArrayHasKey('id', $json[0]);
        $this->assertArrayHasKey('role', $json[0]);
        $this->assertArrayHasKey('status', $json[0]);
        $this->assertArrayHasKey('variants', $json[0]);
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
        $this->assertCount(1, $json);
        $this->assertSame($name, $json[0]['variants'][0]['name']);
    }
}
