<?php

namespace App\Tests\Api\Console\Blog\Route;

use App\Api\Console\Controller\RouteController;
use App\Api\Console\Object\RouteObject;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteController::class)]
#[CoversClass(RouteObject::class)]
class GetRoutesTest extends ApiTestCase
{
    public function test_get_routes(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'route-list'],
            ['status' => 'active'],
        );
        RouteFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'name' => 'Blog Index',
            'match' => '/',
            'template' => 'index',
            'is_enabled' => true,
        ]);

        $this->consoleBlogApi('GET', 'route-list', '/routes', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame('Blog Index', $json[0]['name']);
        $this->assertSame('/', $json[0]['match']);
        $this->assertSame('index', $json[0]['template']);
        $this->assertTrue($json[0]['is_enabled']);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'route-denied'],
            ['status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'route-denied', '/routes', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
