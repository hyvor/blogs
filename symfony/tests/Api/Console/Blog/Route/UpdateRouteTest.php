<?php

namespace App\Tests\Api\Console\Blog\Route;

use App\Api\Console\Controller\RouteController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteController::class)]
class UpdateRouteTest extends ApiTestCase
{
    public function test_update_route(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-update'],
            ['hyvor_user_id' => 603, 'status' => 'active'],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'name' => 'Old Name',
            'match' => '/old',
            'template' => 'old-template',
        ]);

        $authUser = AuthFake::generateUser(['id' => 603]);
        $this->consoleBlogApi('PATCH', 'route-update', '/route/' . $route->getId(), [
            'name' => 'New Name',
            'match' => '/new',
            'template' => 'new-template',
        ], user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('New Name', $json['name']);
        $this->assertSame('/new', $json['match']);
        $this->assertSame('new-template', $json['template']);
    }

    public function test_update_route_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-upd-b1'],
            ['hyvor_user_id' => 604, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-upd-b2'],
            ['hyvor_user_id' => 605, 'status' => 'active'],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 604]);
        $this->consoleBlogApi('PATCH', 'route-upd-b1', '/route/' . $route->getId(), [
            'name' => 'Hacked',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }
}
