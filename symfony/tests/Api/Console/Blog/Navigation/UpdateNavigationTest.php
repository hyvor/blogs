<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\NavigationFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
class UpdateNavigationTest extends ApiTestCase
{
    public function test_update_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-update'],
            ['hyvor_user_id' => 303, 'status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'url' => '/old.json',
            'type' => 'header',
        ]);

        $authUser = AuthFake::generateUser(['id' => 303]);
        $this->consoleBlogApi('PATCH', 'nav-update', '/navigation/' . $nav->getId(), [
            'url' => '/new.json',
            'type' => 'footer',
        ], user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('/new.json', $json['url']);
        $this->assertSame('footer', $json['type']);
    }

    public function test_update_navigation_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-upd-b1'],
            ['hyvor_user_id' => 304, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-upd-b2'],
            ['hyvor_user_id' => 305, 'status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
        ]);

        $authUser = AuthFake::generateUser(['id' => 304]);
        $this->consoleBlogApi('PATCH', 'nav-upd-b1', '/navigation/' . $nav->getId(), [
            'url' => '/hack.json',
            'type' => 'header',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }
}
