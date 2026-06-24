<?php

namespace App\Tests\Api\Console\Blog\Route;

use App\Api\Console\Controller\RouteController;
use App\Api\Console\Object\RouteObject;
use App\Entity\Enum\UserStatus;
use App\Service\Route\Event\RouteChangedEvent;
use App\Service\Route\RouteService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteController::class)]
#[CoversClass(RouteObject::class)]
#[CoversClass(RouteService::class)]
class UpdateRouteTest extends ApiTestCase
{
    public function test_update_route(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-update'],
            ['status' => UserStatus::ACTIVE],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog,
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
        $this->getEd()->assertDispatched(RouteChangedEvent::class);
    }

    public function test_update_to_null(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-update-null'],
            ['status' => UserStatus::ACTIVE],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog,
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
            ['status' => UserStatus::ACTIVE],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-upd-b2'],
            ['status' => UserStatus::ACTIVE],
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
