<?php

namespace App\Tests\Api\Console\Blog\Route;

use App\Api\Console\Controller\RouteController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteController::class)]
class UpdateRouteTest extends ApiTestCase
{
    public function test_update_route(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-update'],
            ['status' => 'active'],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'name' => 'Old Name',
            'match' => '/old',
            'template' => 'old-template',
        ]);

        $this->consoleBlogApi('PATCH', 'route-update', '/route/' . $route->getId(), [
            'name' => 'New Name',
            'match' => '/new',
            'template' => 'new-template',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('New Name', $json['name']);
        $this->assertSame('/new', $json['match']);
        $this->assertSame('new-template', $json['template']);
    }

    public function test_update_to_null(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-update-null'],
            ['status' => 'active'],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'posts_filter' => 'some-filter',
            'content_type' => 'some-type',
        ]);

        $this->consoleBlogApi('PATCH', 'route-update-null', '/route/' . $route->getId(), [
            'posts_filter' => null,
            'content_type' => null,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['posts_filter']);
        $this->assertNull($json['content_type']);
    }

    public function test_update_route_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-upd-b1'],
            ['status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-upd-b2'],
            ['status' => 'active'],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $this->consoleBlogApi('PATCH', 'route-upd-b1', '/route/' . $route->getId(), [
            'name' => 'Hacked',
        ], user: $user1);

        $this->assertResponseStatusCodeSame(404);
    }
}
