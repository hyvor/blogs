<?php

namespace App\Tests\Api\Console\Blog\Route;

use App\Api\Console\Controller\RouteController;
use App\Api\Console\Object\RouteObject;
use App\Entity\Enum\UserStatus;
use App\Entity\Route;
use App\Service\Route\Event\RouteChangedEvent;
use App\Service\Route\RouteService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteController::class)]
#[CoversClass(RouteObject::class)]
#[CoversClass(RouteService::class)]
class CreateRouteTest extends ApiTestCase
{
    public function test_create_route(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-create'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', 'route-create', '/route', [
            'name' => 'Tag Index',
            'match' => '/tag/{slug}',
            'template' => 'tag',
            'posts_filter' => 'tags:{{slug}}',
            'content_type' => null,
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('Tag Index', $json['name']);
        $this->assertSame('/tag/{slug}', $json['match']);
        $this->assertSame('tag', $json['template']);
        $this->assertSame('tags:{{slug}}', $json['posts_filter']);
        $this->assertTrue($json['is_enabled']);

        $route = $this->getEm()->getRepository(Route::class)->findAll();
        $this->assertCount(1, $route);
        $route = $route[0];
        $this->assertSame($blog->getId(), $route->getBlog()->getId());
        $this->assertSame('Tag Index', $route->getName());
        $this->assertSame('/tag/{slug}', $route->getMatch());
        $this->assertSame('tag', $route->getTemplate());
        $this->assertSame('tags:{{slug}}', $route->getPostsFilter());
        $this->assertNull($route->getContentType());
        $this->getEd()->assertDispatched(RouteChangedEvent::class);
    }

    public function test_create_route_limit(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-limit'],
            ['status' => UserStatus::ACTIVE],
        );
        for ($i = 0; $i < 50; $i++) {
            RouteFactory::createOne([
                'blog' => $blog,
                'name' => 'Route ' . $i,
            ]);
        }

        $this->consoleBlogApi('POST', 'route-limit', '/route', [
            'name' => 'Over Limit Route',
            'match' => '/over-limit',
            'template' => 'page',
        ], user: $user);

        $this->assertResponseFailed(422, 'You have reached the maximum number of routes (50)');
    }
}
