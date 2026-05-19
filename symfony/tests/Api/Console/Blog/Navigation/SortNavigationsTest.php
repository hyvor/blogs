<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\NavigationFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
class SortNavigationsTest extends ApiTestCase
{
    public function test_sort_navigations(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-sort'],
            ['hyvor_user_id' => 302, 'status' => 'active'],
        );
        $nav1 = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'sort' => 0,
        ]);
        $nav2 = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'sort' => 1,
        ]);

        $authUser = AuthFake::generateUser(['id' => 302]);
        $this->consoleBlogApi('PATCH', 'nav-sort', '/navigations/sort', [
            'ids' => [$nav2->getId(), $nav1->getId()],
        ], user: $authUser);

        $this->assertResponseIsSuccessful();
    }
}
