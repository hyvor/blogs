<?php

namespace App\Tests\Api\Console\Blog\Route;

use App\Api\Console\Controller\RouteController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteController::class)]
class DeleteRouteTest extends ApiTestCase
{
    public function test_delete_route(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-delete'],
            ['hyvor_user_id' => 606, 'status' => 'active'],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 606]);
        $this->consoleBlogApi('DELETE', 'route-delete', '/route/' . $route->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
    }

    public function test_delete_route_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-del-b1'],
            ['hyvor_user_id' => 607, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-del-b2'],
            ['hyvor_user_id' => 608, 'status' => 'active'],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 607]);
        $this->consoleBlogApi('DELETE', 'route-del-b1', '/route/' . $route->getId(), user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }
}
