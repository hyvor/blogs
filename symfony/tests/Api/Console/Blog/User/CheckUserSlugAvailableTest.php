<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
class CheckUserSlugAvailableTest extends ApiTestCase
{
    public function test_slug_is_available(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'check-user-slug-avail']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'slug' => 'my-slug']);

        $this->consoleBlogApi('GET', $blog, '/user/' . $user->getId() . '/slug-available?slug=other-slug', user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertTrue($this->getJson()['available']);
    }

    public function test_slug_is_not_available_when_used_by_another_user(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'check-user-slug-taken']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'slug' => 'my-slug']);
        UserFactory::createOne(['blog' => $blog, 'slug' => 'other-slug']);

        $this->consoleBlogApi('GET', $blog, '/user/' . $user->getId() . '/slug-available?slug=other-slug', user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertFalse($this->getJson()['available']);
    }

    public function test_slug_is_available_when_it_belongs_to_the_same_user(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'check-user-slug-self']);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $user = UserFactory::createOne(['blog' => $blog, 'slug' => 'my-slug']);

        $this->consoleBlogApi('GET', $blog, '/user/' . $user->getId() . '/slug-available?slug=my-slug', user: $owner);

        $this->assertResponseIsSuccessful();
        $this->assertTrue($this->getJson()['available']);
    }
}
