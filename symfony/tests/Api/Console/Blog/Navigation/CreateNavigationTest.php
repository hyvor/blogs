<?php

namespace App\Tests\Api\Console\Blog\Navigation;

use App\Api\Console\Controller\NavigationController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NavigationController::class)]
class CreateNavigationTest extends ApiTestCase
{
    public function test_create_navigation(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'nav-create'],
            ['hyvor_user_id' => 301, 'status' => 'active'],
        );
        LanguageFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'code' => 'en',
            'is_primary' => true,
        ]);

        $authUser = AuthFake::generateUser(['id' => 301]);
        $this->consoleBlogApi('POST', 'nav-create', '/navigation', [
            'url' => '/header.json',
            'name' => 'Header Nav',
            'type' => 'header',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('/header.json', $json['url']);
        $this->assertSame('header', $json['type']);
        $this->assertCount(1, $json['variants']);
        $this->assertSame('Header Nav', $json['variants'][0]['name']);
    }
}
