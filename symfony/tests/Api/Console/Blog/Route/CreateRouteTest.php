<?php

namespace App\Tests\Api\Console\Blog\Route;

use App\Api\Console\Controller\RouteController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteController::class)]
class CreateRouteTest extends ApiTestCase
{
    public function test_create_route(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-create'],
            ['hyvor_user_id' => 601, 'status' => 'active'],
        );

        $authUser = AuthFake::generateUser(['id' => 601]);
        $this->consoleBlogApi('POST', 'route-create', '/route', [
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
        $this->consoleBlogApi('POST', 'route-limit', '/route', [
            'name' => 'Over Limit Route',
            'match' => '/over-limit',
            'template' => 'page',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }
}
