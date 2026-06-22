<?php

namespace App\Tests\Api\Console\Blog\Route;

use App\Api\Console\Controller\RouteController;
use App\Entity\Route;
use App\Service\Route\Event\RouteChangedEvent;
use App\Service\Route\RouteService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteController::class)]
#[CoversClass(RouteService::class)]
class DeleteRouteTest extends ApiTestCase
{
    public function test_delete_route(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-delete'],
            ['status' => 'active'],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog,
        ]);

        $this->consoleBlogApi('DELETE', 'route-delete', '/route/' . $route->getId(), user: $user);

        $this->assertResponseIsSuccessful();

        $routes = $this->getEm()->getRepository(Route::class)->findAll();
        $this->assertCount(0, $routes);
        $this->getEd()->assertDispatched(RouteChangedEvent::class);
    }

    public function test_delete_route_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-del-b1'],
            ['status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-del-b2'],
            ['status' => 'active'],
        );
        $route = RouteFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $this->consoleBlogApi('DELETE', 'route-del-b1', '/route/' . $route->getId(), user: $user1);

        $this->assertResponseStatusCodeSame(404);
    }
}
