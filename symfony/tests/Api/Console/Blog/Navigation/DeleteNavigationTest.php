<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\NavigationFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
class DeleteNavigationTest extends ApiTestCase
{
    public function test_delete_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-delete'],
            ['hyvor_user_id' => 306, 'status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 306]);
        $this->consoleBlogApi('DELETE', 'nav-delete', '/navigation/' . $nav->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
    }
}
