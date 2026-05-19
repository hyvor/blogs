<?php

namespace Api\Console\Blog;

use App\Api\Console\Controller\RouteController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteController::class)]
class RouteTest extends ApiTestCase
{
    public function test_get_routes(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-list'],
            ['hyvor_user_id' => 600, 'status' => 'active'],
        );
        RouteFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'name' => 'Blog Index',
            'match' => '/',
            'template' => 'index',
            'is_enabled' => true,
        ]);

        $authUser = AuthFake::generateUser(['id' => 600]);
        $this->consoleBlogApi('GET', 'route-list', '/routes', user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json);
        $this->assertCount(1, $json);
        $this->assertSame('Blog Index', $json[0]['name']);
        $this->assertSame('/', $json[0]['match']);
        $this->assertSame('index', $json[0]['template']);
        $this->assertTrue($json[0]['is_enabled']);
    }

    public function test_create_route(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-create'],
            ['hyvor_user_id' => 601, 'status' => 'active'],
        );

        $authUser = AuthFake::generateUser(['id' => 601]);
        $this->consoleBlogApi('POST', 'route-create', '/routes', [
            'name' => 'Tag Index',
            'match' => '/tag/{slug}',
            'template' => 'tag',
            'posts_filter' => 'tags:{{slug}}',
            'content_type' => null,
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('Tag Index', $json['name']);
        $this->assertSame('/tag/{slug}', $json['match']);
        $this->assertSame('tag', $json['template']);
        $this->assertSame('tags:{{slug}}', $json['posts_filter']);
        $this->assertTrue($json['is_enabled']);
    }

    public function test_create_route_limit(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-limit'],
            ['hyvor_user_id' => 602, 'status' => 'active'],
        );
        for ($i = 0; $i < 50; $i++) {
            RouteFactory::createOne([
                'blog' => $blog,
                'blog_id' => $blog->getId(),
                'name' => 'Route ' . $i,
            ]);
        }

        $authUser = AuthFake::generateUser(['id' => 602]);
        $this->consoleBlogApi('POST', 'route-limit', '/routes', [
            'name' => 'Over Limit Route',
            'match' => '/over-limit',
            'template' => 'page',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }

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
        $this->consoleBlogApi('PATCH', 'route-update', '/routes/' . $route->getId(), [
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
        $this->consoleBlogApi('PATCH', 'route-upd-b1', '/routes/' . $route->getId(), [
            'name' => 'Hacked',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }

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
        $this->consoleBlogApi('DELETE', 'route-delete', '/routes/' . $route->getId(), user: $authUser);

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
        $this->consoleBlogApi('DELETE', 'route-del-b1', '/routes/' . $route->getId(), user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-denied'],
            ['hyvor_user_id' => 609, 'status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'route-denied', '/routes', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
