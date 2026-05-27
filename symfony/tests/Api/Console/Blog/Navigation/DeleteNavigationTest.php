<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\NavigationFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
class DeleteNavigationTest extends ApiTestCase
{
    public function test_delete_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-delete'],
            ['status' => 'active'],
        );
        $nav = NavigationFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
        ]);

        $this->consoleBlogApi('DELETE', 'nav-delete', '/navigation/' . $nav->getId(), user: $user);

        $this->assertResponseIsSuccessful();
    }
}
